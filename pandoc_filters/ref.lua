-- Improved Ref filter: Convert Sphinx :ref: expressions to markdown links
-- Handles both cross-file and self-file references properly

-- Get destination context and folder from environment
local destination_context = os.getenv("DESTINATION_CONTEXT") or ""
local destination_folder = os.getenv("DESTINATION_FOLDER") or ""

-- Caches
local anchor_cache = nil
local current_file_anchors = nil

-- Helper function to calculate relative path between two files
local function calculate_relative_path(from_file, to_file)
    -- Remove .md extension and split paths into parts
    from_file = from_file:gsub("%.md$", "")
    to_file = to_file:gsub("%.md$", "")

    local from_parts = {}
    local to_parts = {}

    -- Split from_file path into parts (excluding filename)
    local from_dir = from_file:match("(.+)/[^/]+$") or ""
    if from_dir ~= "" then
        for part in from_dir:gmatch("[^/]+") do
            table.insert(from_parts, part)
        end
    end

    -- Split to_file path into parts
    for part in to_file:gmatch("[^/]+") do
        table.insert(to_parts, part)
    end

    -- Calculate how many directories to go up
    local up_count = #from_parts

    -- Build relative path
    local relative_parts = {}
    for i = 1, up_count do
        table.insert(relative_parts, "..")
    end

    -- Add the target path parts
    for _, part in ipairs(to_parts) do
        table.insert(relative_parts, part)
    end

    if #relative_parts == 0 then
        return to_file
    else
        return table.concat(relative_parts, "/")
    end
end

-- Helper function to build anchor index from all RST files
local function build_anchor_index()
    if anchor_cache then
        return anchor_cache
    end

    anchor_cache = {}

    local function scan_file(file_path)
        local file = io.open(file_path, "r")
        if not file then
            return
        end

        local content = file:read("*all")
        file:close()

        -- Find anchor definitions: .. _anchor-name:
        for anchor in content:gmatch("%.%. _([^:]+):") do
            -- Convert RST file path to corresponding MD path
            local md_path = file_path:gsub("%.rst$", "")

            -- Remove the legacy directory path prefix to get just the relative path within docs
            -- Handle different path patterns that could appear depending on working directory
            if md_path:match("^%./") then
                -- Remove leading "./" (when run from legacy/en/)
                md_path = md_path:gsub("^%./", "")
            elseif md_path:match("^%.%./") then
                -- Remove leading "../" (when run from legacy/en/subdir/)
                md_path = md_path:gsub("^%.%./", "")
            elseif md_path:match("^legacy/en/") then
                -- Remove "legacy/en/" prefix (when run from project root)
                md_path = md_path:gsub("^legacy/en/", "")
            elseif md_path:match("legacy/en/") then
                -- Remove any "legacy/en/" portion from anywhere in path
                md_path = md_path:gsub("^.*/legacy/en/", "")
            end

            anchor_cache[anchor] = md_path
        end
    end

    local function scan_directory(dir)
        local handle = io.popen("find " .. dir .. " -name '*.rst' 2>/dev/null")
        if handle then
            for file in handle:lines() do
                scan_file(file)
            end
            handle:close()
        end
    end

    -- The convert script runs from the RST file directory (like legacy/en/intro/)
    -- We need to scan the legacy/en directory, which could be at different relative paths
    -- depending on how deep we are in the directory structure

    local possible_legacy_paths = {
        ".",         -- When run from legacy/en/ directory
        "../",       -- When run from legacy/en/subdirectory/ (like intro/, development/, etc)
        "../../",    -- When run from legacy/en/subdir/subdir/
        "legacy/en", -- When run from project root
        "../../legacy/en"  -- Fallback for other contexts
    }

    local legacy_dir = nil
    for _, path in ipairs(possible_legacy_paths) do
        local test_file = io.open(path .. "/index.rst", "r")
        if test_file then
            test_file:close()
            legacy_dir = path
            break
        end
    end

    if legacy_dir then
        scan_directory(legacy_dir)
    end
    return anchor_cache
end

-- Helper function to extract anchors from current file
local function get_current_file_anchors()
    if current_file_anchors then
        return current_file_anchors
    end

    current_file_anchors = {}

    -- Find the current RST file being processed
    -- The convert script creates temp files with random names in the RST directory
    -- We need to find any .rst file in the current directory
    local handle = io.popen("find . -maxdepth 1 -name '*.rst' -type f 2>/dev/null")
    if handle then
        for file_path in handle:lines() do
            local file = io.open(file_path, "r")
            if file then
                local content = file:read("*all")
                file:close()


                -- Extract anchors from this specific file
                for anchor in content:gmatch("%.%. _([^:]+):") do
                    current_file_anchors[anchor] = true
                end
                break -- Only process the first (and should be only) .rst file found
            end
        end
        handle:close()
    end

    return current_file_anchors
end

-- Helper function to resolve ref target to markdown link
local function resolve_ref_link(ref_content)
    -- Pattern 1: "label <target>" (with custom label)
    local label, target = ref_content:match("^(.-)%s*<%s*([^>]+)%s*>$")
    if label and target then
        label = label:gsub("^%s+", ""):gsub("%s+$", "")
        target = target:gsub("^%s+", ""):gsub("%s+$", "")

        -- Check if anchor is in current file first
        local current_anchors = get_current_file_anchors()
        if current_anchors[target] then
            return pandoc.Link(label, "#" .. target)
        end

        -- Look up target in global anchor cache
        local anchors = build_anchor_index()
        local target_file = anchors[target]

        if target_file then
            -- Calculate relative path from current file to target
            local current_file = destination_context

            -- Extract the base name of the destination folder (e.g., "docs/5/en")
            local folder_basename = destination_folder:match("([^/]+/[^/]+/[^/]+)$")

            if folder_basename then
                -- Escape special regex characters and remove folder basename prefix
                local escaped_basename = folder_basename:gsub("[%-%.%+%[%]%(%)%$%^%%%?%*]", "%%%1")
                if current_file:match("^" .. escaped_basename .. "/") then
                    current_file = current_file:gsub("^" .. escaped_basename .. "/", "")
                end
            end

            local link_path = calculate_relative_path(current_file, target_file)
            return pandoc.Link(label, link_path .. "#" .. target)
        else
            -- io.stderr:write("Warning: Anchor '" .. target .. "' not found for ref '" .. ref_content .. "'\n")
            return pandoc.Link(label, "#" .. target)
        end
    end

    -- Pattern 2: "target" only (no custom label)
    local target = ref_content:gsub("^%s+", ""):gsub("%s+$", "")

    -- Check if anchor is in current file first
    local current_anchors = get_current_file_anchors()
    if current_anchors[target] then
        local label = target:gsub("[-_]", " "):gsub("(%w)(%w*)", function(first, rest)
            return first:upper() .. rest:lower()
        end)
        return pandoc.Link(label, "#" .. target)
    end

    -- Look up target in global anchor cache
    local anchors = build_anchor_index()
    local target_file = anchors[target]

    if target_file then
        -- Calculate relative path from current file to target
        local current_file = destination_context

        -- Extract the base name of the destination folder (e.g., "docs/5/en")
        local folder_basename = destination_folder:match("([^/]+/[^/]+/[^/]+)$")

        if folder_basename then
            -- Escape special regex characters and remove folder basename prefix
            local escaped_basename = folder_basename:gsub("[%-%.%+%[%]%(%)%$%^%%%?%*]", "%%%1")
            if current_file:match("^" .. escaped_basename .. "/") then
                current_file = current_file:gsub("^" .. escaped_basename .. "/", "")
            end
        end

        local link_path = calculate_relative_path(current_file, target_file)

        local label = target:gsub("[-_]", " "):gsub("(%w)(%w*)", function(first, rest)
            return first:upper() .. rest:lower()
        end)
        return pandoc.Link(label, link_path .. "#" .. target)
    else
        -- io.stderr:write("Warning: Anchor '" .. target .. "' not found for ref '" .. ref_content .. "'\n")
        local label = target:gsub("[-_]", " "):gsub("(%w)(%w*)", function(first, rest)
            return first:upper() .. rest:lower()
        end)
        return pandoc.Link(label, "#" .. target)
    end
end

-- Process Code elements with interpreted-text class and role="ref"
function Code(elem)
    if elem.classes:includes("interpreted-text") then
        for _, attr in ipairs(elem.attributes) do
            if attr[1] == "role" and attr[2] == "ref" then
                return resolve_ref_link(elem.text)
            end
        end
    end
    return elem
end
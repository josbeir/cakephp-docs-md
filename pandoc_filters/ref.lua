-- Improved Ref filter: Convert Sphinx :ref: expressions to markdown links
-- Handles both cross-file and self-file references properly

-- Get destination context, folder, and source folder from environment
local destination_context = os.getenv("DESTINATION_CONTEXT") or ""
local destination_folder = os.getenv("DESTINATION_FOLDER") or ""
local source_folder = os.getenv("SOURCE_FOLDER") or ""
local current_source_file = os.getenv("CURRENT_SOURCE_FILE") or ""

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

            -- Check if this is a temporary file and map it to the actual filename
            local basename = md_path:match("([^/]+)$")
            if basename and basename:match("^tmp%.") then
                -- This is a temporary file, we need to find the corresponding original file
                -- Extract the directory and find the actual RST file in the same directory
                local dir = md_path:match("^(.+)/[^/]+$") or "."
                local original_handle = io.popen("find " .. dir .. " -maxdepth 1 -name '*.rst' -type f ! -name 'tmp.*' 2>/dev/null")
                if original_handle then
                    local original_file = original_handle:read("*line")
                    original_handle:close()
                    if original_file then
                        -- Use the original filename instead of the temp filename
                        md_path = original_file:gsub("%.rst$", "")
                    end
                end
            end

            -- Remove the source directory path prefix to get just the relative path within docs
            if source_folder and source_folder ~= "" then
                -- Use the actual source folder to determine what to strip
                local source_basename = source_folder:match("([^/]+)$") or ""

                -- Handle absolute paths by making them relative to source folder
                if md_path:find(source_folder, 1, true) == 1 then
                    -- Remove the full source folder path
                    md_path = md_path:sub(#source_folder + 2) -- +2 to remove the trailing slash too
                elseif md_path:match("^%./") then
                    -- Remove leading "./"
                    md_path = md_path:gsub("^%./", "")
                elseif md_path:match("^%.%./") then
                    -- Remove leading "../"
                    md_path = md_path:gsub("^%.%./", "")
                elseif source_basename ~= "" then
                    -- Remove any occurrence of the source folder basename from the path
                    local pattern = source_basename .. "/"
                    md_path = md_path:gsub("^.*/" .. pattern, "")
                    md_path = md_path:gsub("^" .. pattern, "")
                end
            else
                -- Fallback behavior when SOURCE_FOLDER is not available
                if md_path:match("^%./") then
                    md_path = md_path:gsub("^%./", "")
                elseif md_path:match("^%.%./") then
                    md_path = md_path:gsub("^%.%./", "")
                end
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

    -- Use the SOURCE_FOLDER passed from the convert script
    -- This ensures we always scan the correct source directory regardless of working directory
    if source_folder and source_folder ~= "" then
        scan_directory(source_folder)
    else
        -- Fallback to current directory if SOURCE_FOLDER is not set (for backward compatibility)
        scan_directory(".")
    end
    return anchor_cache
end

-- Helper function to extract anchors from current file
local function get_current_file_anchors()
    if current_file_anchors then
        return current_file_anchors
    end

    current_file_anchors = {}

    -- Use the specific current source file if provided
    if current_source_file and current_source_file ~= "" then
        local file = io.open(current_source_file, "r")
        if file then
            local content = file:read("*all")
            file:close()

            -- Extract anchors from this specific file
            for anchor in content:gmatch("%.%. _([^:]+):") do
                current_file_anchors[anchor] = true
            end
        end
    else
        -- Fallback to original logic when CURRENT_SOURCE_FILE is not available
        -- Find the current RST file being processed
        -- The convert script creates temp files with random names in the RST directory
        -- We need to find the .rst file, but exclude temporary files (which have tmp. prefix)
        local handle = io.popen("find . -maxdepth 1 -name '*.rst' -type f ! -name 'tmp.*' 2>/dev/null")
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

        -- If no non-temp files found, fallback to temp files but extract the actual content
        if not next(current_file_anchors) then
            local temp_handle = io.popen("find . -maxdepth 1 -name 'tmp.*.rst' -type f 2>/dev/null")
            if temp_handle then
                for file_path in temp_handle:lines() do
                    local file = io.open(file_path, "r")
                    if file then
                        local content = file:read("*all")
                        file:close()

                        -- Extract anchors from this specific file
                        for anchor in content:gmatch("%.%. _([^:]+):") do
                            current_file_anchors[anchor] = true
                        end
                        break -- Only process the first temp file found
                    end
                end
                temp_handle:close()
            end
        end
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

            -- Normalize current_file to be relative to destination_folder
            if destination_folder and destination_folder ~= "" then
                -- If current_file starts with destination_folder, make it relative
                if current_file:find(destination_folder, 1, true) == 1 then
                    current_file = current_file:sub(#destination_folder + 2) -- +2 to remove trailing slash
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

        -- Normalize current_file to be relative to destination_folder
        if destination_folder and destination_folder ~= "" then
            -- If current_file starts with destination_folder, make it relative
            if current_file:find(destination_folder, 1, true) == 1 then
                current_file = current_file:sub(#destination_folder + 2) -- +2 to remove trailing slash
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
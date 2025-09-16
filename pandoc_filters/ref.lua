-- Improved Ref filter: Convert Sphinx :ref: expressions to markdown links
-- Handles both cross-file and self-file references properly

-- Get destination context and folder from environment
local destination_context = os.getenv("DESTINATION_CONTEXT") or ""
local destination_folder = os.getenv("DESTINATION_FOLDER") or ""

-- Caches
local anchor_cache = nil
local current_file_anchors = nil

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
            local md_path = file_path

            -- Handle different path patterns based on working directory
            if md_path:match("^legacy/en/") then
                -- When scanning from project root
                md_path = md_path:gsub("^legacy/en/", "")
                md_path = md_path:gsub("%.rst$", ".md")
            elseif md_path:match("^%.%./%.%./legacy/en/") then
                -- When scanning from deeper subdirectories
                md_path = md_path:gsub("^%.%./%.%./legacy/en/", "")
                md_path = md_path:gsub("%.rst$", ".md")
            elseif md_path:match("^%.%./legacy/en/") then
                -- When scanning from sibling directories
                md_path = md_path:gsub("^%.%./legacy/en/", "")
                md_path = md_path:gsub("%.rst$", ".md")
            elseif md_path:match("^%./") then
                -- When scanning from legacy/en/ directory (convert script context)
                md_path = md_path:gsub("^%./", "")
                md_path = md_path:gsub("%.rst$", ".md")
            else
                -- Default case - remove any leading ../ and convert to .md
                md_path = md_path:gsub("^%.%./", "")
                md_path = md_path:gsub("%.rst$", ".md")
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

    -- Try multiple possible legacy paths - order matters for working directory context
    local possible_legacy_paths = {
        ".",                 -- When run from legacy/en/ directory (convert script context)
        "legacy/en",         -- When run from project root
        "../../legacy/en",   -- When run from deeper subdirectories
        "../legacy/en"       -- When run from sibling directories
    }

    for _, path in ipairs(possible_legacy_paths) do
        local test_file = io.open(path .. "/index.rst", "r")
        if test_file then
            test_file:close()
            scan_directory(path)
            break
        end
    end

    return anchor_cache
end

-- Helper function to extract anchors from current file
local function get_current_file_anchors()
    if current_file_anchors then
        return current_file_anchors
    end

    current_file_anchors = {}

    -- Find the current RST file more specifically
    -- The convert script creates temp files, so look for the right one
    local current_base_name = destination_context:gsub("^.*/", ""):gsub("%.md$", "")

    local possible_files = {
        current_base_name .. ".rst",
        "./" .. current_base_name .. ".rst",
    }

    -- Also try to find any temp files that might match
    local handle = io.popen("find . -maxdepth 1 -name '*.rst' -type f 2>/dev/null")
    if handle then
        for file_path in handle:lines() do
            table.insert(possible_files, file_path)
        end
        handle:close()
    end

    -- Try each possible file, but only read the FIRST one found
    -- This prevents mixing anchors from multiple files
    for _, file_path in ipairs(possible_files) do
        local file = io.open(file_path, "r")
        if file then
            local content = file:read("*all")
            file:close()

            -- Extract anchors from this specific file
            for anchor in content:gmatch("%.%. _([^:]+):") do
                current_file_anchors[anchor] = true
            end
            break -- Only process the first file found
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
            return pandoc.Link(label, target_file .. "#" .. target)
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
        local label = target:gsub("[-_]", " "):gsub("(%w)(%w*)", function(first, rest)
            return first:upper() .. rest:lower()
        end)
        return pandoc.Link(label, target_file .. "#" .. target)
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
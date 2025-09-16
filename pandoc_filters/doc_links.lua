-- Doc links filter: Convert Sphinx :doc: directives to markdown links
-- This filter handles :doc:`target` and :doc:`label <target>` patterns

-- Get destination context and folder from environment
local destination_context = os.getenv("DESTINATION_CONTEXT") or ""
local destination_folder = os.getenv("DESTINATION_FOLDER") or ""

-- Cache for file titles to avoid re-reading
local title_cache = {}


-- Helper function to extract title from a markdown or RST file
local function get_file_title(file_path)
    if title_cache[file_path] then
        return title_cache[file_path]
    end

    -- Clean the file path
    local clean_path = file_path:gsub("^/", "")

    -- Try both .md and .rst extensions in destination folder
    local possible_paths = {
        destination_folder .. "/" .. clean_path .. ".md",
        destination_folder .. "/" .. clean_path .. ".rst"
    }

    for _, path in ipairs(possible_paths) do
        local file = io.open(path, "r")
        if file then
            local content = file:read("*all")
            file:close()

            -- Check if content was read successfully
            if not content then
                goto continue
            end

            -- Try to extract title from various formats
            local title = nil

            -- Look for markdown # header
            title = content:match("^#%s+([^\n]+)")
            if title then
                title_cache[file_path] = title
                return title
            end

            -- Look for RST title (underlined with = or #)
            title = content:match("([^\n]+)\n[=#]+")
            if title then
                title = title:gsub("^%s+", ""):gsub("%s+$", "") -- trim whitespace
                title_cache[file_path] = title
                return title
            end

            -- Look for RST title (overlined and underlined with = or #)
            title = content:match("[=#]+\n([^\n]+)\n[=#]+")
            if title then
                title = title:gsub("^%s+", ""):gsub("%s+$", "") -- trim whitespace
                title_cache[file_path] = title
                return title
            end

            break -- Found the file, stop looking
        end
        ::continue::
    end

    -- Fallback: use filename as title
    local filename = file_path:match("([^/]+)$") or file_path
    filename = filename:gsub("%.md$", ""):gsub("%-", " "):gsub("_", " ")
    -- Capitalize first letter of each word
    filename = filename:gsub("(%w)(%w*)", function(first, rest)
        return first:upper() .. rest:lower()
    end)

    title_cache[file_path] = filename
    return filename
end

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
        return to_file .. ".md"
    else
        return table.concat(relative_parts, "/") .. ".md"
    end
end

-- Helper function to resolve :doc: target to markdown link with correct relative path
local function resolve_doc_link(target_path, custom_label)
    -- Handle special case of root path
    if target_path == "/" or target_path == "" then
        target_path = "index"
    end

    -- Check if this is a same-directory link (starts with ./)
    if target_path:match("^%./") then
        -- Same directory link - just remove ./ and add .md
        local clean_target = target_path:gsub("^%./", "")
        local link_path = clean_target .. ".md"

        -- Get title - use custom label if provided, otherwise extract from file
        local title = custom_label
        if not title or title == "" then
            title = get_file_title(clean_target)
        end

        return link_path, title
    end

    -- Clean target path - remove leading slash
    local clean_target = target_path:gsub("^/", "")

    -- Calculate relative path from current file to target
    -- Extract the relative path from destination context using destination folder
    local current_file = destination_context

    -- Extract the base name of the destination folder (e.g., "docs/5/en")
    local folder_basename = destination_folder:match("([^/]+/[^/]+/[^/]+)$")

    if folder_basename then
        -- Escape special regex characters in folder_basename
        local escaped_basename = folder_basename:gsub("[%-%.%+%[%]%(%)%$%^%%%?%*]", "%%%1")
        -- Remove the folder basename prefix if present
        if current_file:match("^" .. escaped_basename .. "/") then
            current_file = current_file:gsub("^" .. escaped_basename .. "/", "")
        end
    end

    local link_path = calculate_relative_path(current_file, clean_target)

    -- Get title - use custom label if provided, otherwise extract from file
    local title = custom_label
    if not title or title == "" then
        title = get_file_title(clean_target)
    end

    return link_path, title
end

-- Process Code elements with interpreted-text class and role="doc"
function Code(elem)
    -- Check if this is a doc role
    if elem.classes:includes("interpreted-text") then
        for _, attr in ipairs(elem.attributes) do
            if attr[1] == "role" and attr[2] == "doc" then
                local text = elem.text

                -- Pattern 1: "label <target>" (with custom label)
                local label, target = text:match("^(.-)%s*<%s*([^>]+)%s*>$")
                if label and target then
                    local relative_path, title = resolve_doc_link(target, label:gsub("^%s+", ""):gsub("%s+$", ""))
                    return pandoc.Link(title, relative_path)
                end

                -- Pattern 2: "target" only (no custom label)
                local relative_path, title = resolve_doc_link(text, nil)
                return pandoc.Link(title, relative_path)
            end
        end
    end

    return elem
end
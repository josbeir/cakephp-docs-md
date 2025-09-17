-- Toctree filter: Convert Sphinx toctree directives to markdown lists
-- Handles :caption:, :hidden:, and :maxdepth: options
-- Uses page titles as link labels

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

    -- Try both .md and .rst extensions in destination folder first
    local possible_paths = {
        destination_folder .. "/" .. clean_path .. ".md",
        destination_folder .. "/" .. clean_path .. ".rst",
        -- Also try in legacy folder for RST files
        "legacy/en/" .. clean_path .. ".rst"
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

            -- Look for RST title (underlined with various characters)
            title = content:match("([^\n]+)\n[=#*+-~^'\"`:;<>.,?!@$%%&()_]+")
            if title then
                title = title:gsub("^%s+", ""):gsub("%s+$", "") -- trim whitespace
                title_cache[file_path] = title
                return title
            end

            -- Look for RST title (overlined and underlined)
            title = content:match("[=#*+-~^'\"`:;<>.,?!@$%%&()_]+\n([^\n]+)\n[=#*+-~^'\"`:;<>.,?!@$%%&()_]+")
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
    filename = filename:gsub("%.md$", ""):gsub("%.rst$", ""):gsub("%-", " "):gsub("_", " ")
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
        return to_file
    else
        return table.concat(relative_parts, "/")
    end
end

-- Helper function to create a markdown link with proper relative path
local function create_markdown_link(file_path)
    -- Check if this is an external link in the format "Label <URL>"
    local label, url = file_path:match("^(.-)%s*<%s*([^>]+)%s*>$")
    if label and url then
        -- This is an external link - use the provided label and URL
        label = label:gsub("^%s+", ""):gsub("%s+$", "") -- trim whitespace
        return pandoc.Link(label, url)
    end

    -- Check if this is a plain URL (starts with http:// or https://)
    if file_path:match("^https?://") then
        -- Extract domain name or use URL as title
        local domain = file_path:match("https?://([^/]+)")
        local title = domain or file_path
        return pandoc.Link(title, file_path)
    end

    -- Regular internal file link
    local target_path = file_path
    local title = get_file_title(file_path)

    -- Check if this is a same-directory link (starts with ./)
    if target_path:match("^%./") then
        -- Same directory link - just remove ./
        local clean_target = target_path:gsub("^%./", "")
        local link_path = clean_target
        return pandoc.Link(title, link_path)
    end

    -- Calculate relative path from current file to target
    -- Extract the relative path from destination context using destination folder
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

    -- Clean target path - remove leading slash
    local clean_target = target_path:gsub("^/", "")

    local link_path = calculate_relative_path(current_file, clean_target)

    return pandoc.Link(title, link_path)
end

-- Process Div elements to find toctree directives
function Div(elem)
    -- Check if this is a toctree directive
    if elem.classes:includes("toctree") then
        -- Parse toctree attributes
        local caption = nil
        local hidden = false
        local maxdepth = nil

        for _, attr in ipairs(elem.attributes) do
            if attr[1] == "caption" then
                caption = attr[2]
            elseif attr[1] == "hidden" then
                hidden = true
            elseif attr[1] == "maxdepth" then
                maxdepth = tonumber(attr[2])
            end
        end

        -- Don't render if hidden
        if hidden then
            return {}
        end

        -- Extract file paths from the content
        local file_list = {}
        for _, block in ipairs(elem.content) do
            if block.t == "Para" then
                local i = 1
                while i <= #block.content do
                    local inline = block.content[i]

                    if inline.t == "Str" and inline.text then
                        -- Look ahead to see if this might be an external link
                        local label_parts = {inline.text}
                        local j = i + 1
                        local found_link_pattern = false

                        -- Collect consecutive Str and Space elements that might form a label
                        while j <= #block.content do
                            local next_elem = block.content[j]

                            if next_elem.t == "Space" then
                                -- Add a space to the label
                                j = j + 1
                                if j <= #block.content and block.content[j].t == "Str" then
                                    if block.content[j].text == "<" then
                                        -- Found the start of the link pattern
                                        if j + 2 <= #block.content and
                                           block.content[j + 1].t == "Link" and
                                           block.content[j + 2].t == "Str" and block.content[j + 2].text == ">" then
                                            found_link_pattern = true

                                            -- Create external link format
                                            local label = table.concat(label_parts, " ")
                                            local link_elem = block.content[j + 1]
                                            local url = link_elem.target or ""
                                            local external_link_format = label .. " <" .. url .. ">"
                                            table.insert(file_list, external_link_format)

                                            -- Skip to after the ">" element
                                            i = j + 3
                                            break
                                        else
                                            -- Not a link pattern, add this as part of label
                                            table.insert(label_parts, block.content[j].text)
                                            j = j + 1
                                        end
                                    else
                                        -- Regular word, add to label
                                        table.insert(label_parts, block.content[j].text)
                                        j = j + 1
                                    end
                                else
                                    -- Space not followed by Str, break
                                    break
                                end
                            else
                                -- Not a space, end of potential label
                                break
                            end
                        end

                        if not found_link_pattern then
                            -- Regular file path (single word)
                            local file_path = inline.text
                            if file_path and file_path ~= "" then
                                table.insert(file_list, file_path)
                            end
                            i = i + 1
                        end
                    else
                        i = i + 1
                    end
                end
            end
        end

        -- Create the result
        local result = {}

        -- Add caption as a header if present
        if caption then
            table.insert(result, pandoc.Header(3, caption))
        end

        -- Create list of links
        if #file_list > 0 then
            local list_items = {}
            for _, file_path in ipairs(file_list) do
                local link = create_markdown_link(file_path)
                table.insert(list_items, {pandoc.Plain({link})})
            end

            table.insert(result, pandoc.BulletList(list_items))
        end

        -- Return the result
        if #result == 1 then
            return result[1]
        elseif #result > 1 then
            return result
        else
            return {}
        end
    end

    return elem
end
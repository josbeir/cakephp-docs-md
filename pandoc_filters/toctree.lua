-- Toctree filter: Convert Sphinx toctree directives to markdown lists
-- Handles :caption:, :hidden:, and :maxdepth: options
-- Uses page titles as link labels

-- Import shared utilities
-- Get the directory where this filter is located
local filter_dir = debug.getinfo(1, "S").source:match("@(.*/)") or ""
-- Add the filter directory to the package path
package.path = package.path .. ";" .. filter_dir .. "?.lua"
-- Now require utils from the same directory
local utils = require('utils')

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
    local title = utils.get_file_title(file_path)

    -- Check if this is a same-directory link (starts with ./)
    if target_path:match("^%./") then
        -- Same directory link - just remove ./
        local clean_target = target_path:gsub("^%./", "")
        local link_path = clean_target
        return pandoc.Link(title, link_path)
    end

    -- Calculate relative path from current file to target
    local current_file = utils.get_current_file_relative()

    -- Clean target path - remove leading slash
    local clean_target = utils.normalize_path(target_path)

    local link_path = utils.calculate_relative_path(current_file, clean_target)

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
                        -- Start collecting text elements to look for external link pattern
                        local text_elements = {}
                        local j = i
                        local found_external_link = false

                        -- Scan ahead to see if this forms an external link pattern: "Label <URL>"
                        while j <= #block.content do
                            local elem = block.content[j]

                            if elem.t == "Str" then
                                table.insert(text_elements, {type = "str", text = elem.text})
                            elseif elem.t == "Space" then
                                table.insert(text_elements, {type = "space"})
                            elseif elem.t == "Link" then
                                table.insert(text_elements, {type = "link", target = elem.target})
                            else
                                -- Other element types break the pattern
                                break
                            end
                            j = j + 1
                        end

                        -- Now analyze the collected elements to see if they form "Label <URL>" pattern
                        local full_text = ""
                        for k, elem in ipairs(text_elements) do
                            if elem.type == "str" then
                                full_text = full_text .. elem.text
                            elseif elem.type == "space" then
                                full_text = full_text .. " "
                            elseif elem.type == "link" then
                                -- Check if the previous text ends with "<" and we have a ">" after the link
                                if full_text:match("%s*<$") and
                                   k < #text_elements and
                                   text_elements[k + 1] and
                                   text_elements[k + 1].type == "str" and
                                   text_elements[k + 1].text == ">" then

                                    -- This is an external link pattern
                                    local label = utils.trim(full_text:gsub("%s*<$", ""))
                                    local url = elem.target
                                    local external_link_format = label .. " <" .. url .. ">"
                                    table.insert(file_list, external_link_format)
                                    found_external_link = true

                                    -- Skip past all the elements we've processed
                                    i = j
                                    break
                                end
                            end
                        end

                        -- Also check if the full text matches the "Label <URL>" pattern directly
                        if not found_external_link then
                            local label, url = full_text:match("^(.-)%s*<%s*([^>]+)%s*>$")
                            if label and url then
                                label = utils.trim(label)
                                url = utils.trim(url)
                                local external_link_format = label .. " <" .. url .. ">"
                                table.insert(file_list, external_link_format)
                                found_external_link = true
                                -- Skip past all the elements we've processed
                                i = j
                            end
                        end

                        if not found_external_link then
                            -- This is a regular file path (single word)
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
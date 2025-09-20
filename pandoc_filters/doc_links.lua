-- Doc links filter: Convert Sphinx :doc: directives to markdown links
-- This filter handles :doc:`target` and :doc:`label <target>` patterns

-- Import shared utilities
-- Get the directory where this filter is located
local filter_dir = debug.getinfo(1, "S").source:match("@(.*/)") or ""
-- Add the filter directory to the package path
package.path = package.path .. ";" .. filter_dir .. "?.lua"
-- Now require utils from the same directory
local utils = require('utils')

-- Helper function to resolve :doc: target to markdown link with correct relative path
local function resolve_doc_link(target_path, custom_label)
    -- Handle special case of root path
    if target_path == "/" or target_path == "" then
        target_path = "index"
    end

    -- Check if this is a same-directory link (starts with ./)
    if target_path:match("^%./") then
        -- Same directory link - just remove ./
        local clean_target = target_path:gsub("^%./", "")
        local link_path = clean_target

        -- Get title - use custom label if provided, otherwise extract from file
        local title = custom_label
        if not title or title == "" then
            title = utils.get_file_title(clean_target)
        end

        return link_path, title
    end

    -- Clean target path - remove leading slash
    local clean_target = target_path:gsub("^/", "")

    -- Calculate relative path from current file to target
    local current_file = utils.get_current_file_relative()
    local link_path = utils.calculate_relative_path(current_file, clean_target)

    -- Get title - use custom label if provided, otherwise extract from file
    local title = custom_label
    if not title or title == "" then
        title = utils.get_file_title(clean_target)
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
                    local relative_path, title = resolve_doc_link(target, utils.trim(label))
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
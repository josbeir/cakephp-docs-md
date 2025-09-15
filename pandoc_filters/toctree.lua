-- Toctree conversion filter
-- Converts Sphinx toctree directives to markdown link lists

local system = require 'pandoc.system'
local utils = require 'pandoc.utils'

-- Cache for file titles to avoid re-reading
local title_cache = {}

-- Function to extract title from a markdown or RST file
local function get_file_title(file_path, base_dir)
    if title_cache[file_path] then
        return title_cache[file_path]
    end
    
    -- Try both .md and .rst extensions, and with/without leading slash
    local possible_paths = {
        base_dir .. "/" .. file_path .. ".md",
        base_dir .. "/" .. file_path .. ".rst",
        base_dir .. file_path .. ".md", 
        base_dir .. file_path .. ".rst"
    }
    
    -- If path starts with /, try removing it
    if file_path:sub(1,1) == "/" then
        local path_no_slash = file_path:sub(2)
        table.insert(possible_paths, base_dir .. "/" .. path_no_slash .. ".md")
        table.insert(possible_paths, base_dir .. "/" .. path_no_slash .. ".rst")
    else
        -- Also try with leading slash
        table.insert(possible_paths, base_dir .. "/" .. file_path .. ".md")
        table.insert(possible_paths, base_dir .. "/" .. file_path .. ".rst")
    end
    
    for _, path in ipairs(possible_paths) do
        local file = io.open(path, "r")
        if file then
            local content = file:read("*all")
            file:close()
            
            -- Try to extract title from various formats
            local title = nil
            
            -- Look for YAML front matter title
            title = content:match("^%-%-%-.*\ntitle:%s*['\"]?([^'\"\n]+)['\"]?")
            if title then
                title_cache[file_path] = title
                return title
            end
            
            -- Look for markdown # header
            title = content:match("^#%s+([^\n]+)")
            if title then
                title_cache[file_path] = title
                return title
            end
            
            -- Look for RST title (underlined with =)
            title = content:match("([^\n]+)\n=+")
            if title then
                title = title:gsub("^%s+", ""):gsub("%s+$", "") -- trim whitespace
                title_cache[file_path] = title
                return title
            end
            
            -- Look for RST title (overlined and underlined)
            title = content:match("=+\n([^\n]+)\n=+")
            if title then
                title = title:gsub("^%s+", ""):gsub("%s+$", "") -- trim whitespace
                title_cache[file_path] = title
                return title
            end
            
            break -- Found the file, stop looking
        end
    end
    
    -- Fallback: use filename as title
    local filename = file_path:match("([^/]+)$") or file_path
    filename = filename:gsub("%-", " "):gsub("_", " ")
    -- Capitalize first letter of each word
    filename = filename:gsub("(%w)(%w*)", function(first, rest)
        return first:upper() .. rest:lower()
    end)
    
    title_cache[file_path] = filename
    return filename
end

-- Helper function to make paths relative based on common patterns
local function make_relative_path(url)
    -- Handle the specific pattern where we're in a directory and linking to a subdirectory
    -- For example: controllers/components.rst links to controllers/components/flash.md
    -- Should become: components/flash.md
    
    local parts = {}
    for part in url:gmatch("[^/]+") do
        table.insert(parts, part)
    end
    
    if #parts >= 3 then
        -- Check if we have pattern like "controllers/components/flash.md"
        -- where the first two parts form a potential parent directory
        local potential_parent = parts[1] .. "/" .. parts[2]
        
        -- Common patterns in CakePHP docs where we want to make paths relative:
        -- These are cases where files in dir1/ link to dir1/dir2/file.md
        local common_patterns = {
            "controllers/components",
            "views/helpers", 
            "orm/behaviors",
            "console-commands/commands",
            "core-libraries/helpers",
            "tutorials-and-examples/blog",
            "tutorials-and-examples/cms",
            "tutorials-and-examples/bookmarks",
            "appendices/migration-guides"
        }
        
        for _, pattern in ipairs(common_patterns) do
            if potential_parent == pattern then
                -- Remove the first part, keeping everything from the second part onward
                local relative_parts = {}
                for i = 2, #parts do
                    table.insert(relative_parts, parts[i])
                end
                return table.concat(relative_parts, "/")
            end
        end
    end
    
    return url
end

-- Function to convert file path to markdown link
local function path_to_link(file_path)
    -- Remove leading slash and add .md extension for links
    local link_path = file_path
    if link_path:sub(1,1) == "/" then
        link_path = link_path:sub(2)
    end
    link_path = link_path .. ".md"
    
    -- Make the URL relative if appropriate
    link_path = make_relative_path(link_path)
    
    return link_path
end

function Div(elem)
    -- Check if this is a toctree div
    if elem.classes and elem.classes:includes("toctree") then
        local items = {}
        local base_dir = "legacy/en" -- Adjust this path as needed
        
        -- Extract file paths from the div content
        for _, block in ipairs(elem.content) do
            if block.t == "Para" or block.t == "Plain" then
                local current_path = ""
                for _, inline in ipairs(block.content) do
                    if inline.t == "Str" then
                        -- Check if this looks like a file path
                        local path = inline.text:match("^[%w/_%-%.]+$")
                        if path and not path:match("^%s*$") then
                            local title = get_file_title(path, base_dir)
                            local link = path_to_link(path)
                            table.insert(items, pandoc.Plain({
                                pandoc.Str("- "),
                                pandoc.Link(title, link)
                            }))
                        end
                    end
                end
            end
        end
        
        -- Return the list of links as a proper BulletList
        if #items > 0 then
            -- Convert Plain items to list items
            local list_items = {}
            for _, item in ipairs(items) do
                -- Extract the link from the Plain block and create a proper list item
                local link_content = {}
                for _, inline in ipairs(item.content) do
                    if inline.t ~= "Str" or inline.text ~= "- " then
                        table.insert(link_content, inline)
                    end
                end
                table.insert(list_items, {pandoc.Plain(link_content)})
            end
            return pandoc.BulletList(list_items)
        end
    end
    
    return elem
end
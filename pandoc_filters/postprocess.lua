-- Post-process filter: Fix markdown output after pandoc conversion
-- This runs on the final markdown to clean up links and formatting

function Code(elem)
    local text = elem.text
    local original_text = text
    
    -- Handle :doc: pattern: "text </path>"
    text = text:gsub("(.-)%s*<%s*(/[^>]+)%s*>", function(link_text, path)
        local clean_text = link_text:gsub("^%s+", ""):gsub("%s+$", "")
        local clean_path = path:gsub("^/", "")
        if not clean_path:match("%.md$") then
            clean_path = clean_path .. ".md"
        end
        return "[" .. clean_text .. "](" .. clean_path .. ")"
    end)
    
    -- Handle :ref: pattern: "text <reference>" (no leading slash)
    text = text:gsub("(.-)%s*<%s*([^/>][^>]*)%s*>", function(link_text, ref)
        local clean_text = link_text:gsub("^%s+", ""):gsub("%s+$", "")
        local anchor = "#" .. ref:lower():gsub("%s+", "-"):gsub("[^%w%-_]", "")
        return "[" .. clean_text .. "](" .. anchor .. ")"
    end)
    
    -- Handle simple path references without custom text: "/path"
    if text:match("^%s*/[^%s]+%s*$") then
        local path = text:gsub("^%s*", ""):gsub("%s*$", "")
        local clean_path = path:gsub("^/", "")
        if not clean_path:match("%.md$") then
            clean_path = clean_path .. ".md"
        end
        -- Use the path as link text, but make it more readable
        local link_text = clean_path:gsub("%.md$", ""):gsub("/", " / "):gsub("%-", " "):gsub("^%l", string.upper)
        text = "[" .. link_text .. "](" .. clean_path .. ")"
    end
    
    if text ~= original_text then
        return pandoc.RawInline("markdown", text)
    end
    
    return elem
end

function Str(elem)
    local text = elem.text
    
    -- Fix escaped brackets in links that were processed by pandoc
    text = text:gsub("\\%[", "[")
    text = text:gsub("\\%]", "]")
    
    if text ~= elem.text then
        return pandoc.Str(text)
    end
    
    return elem
end
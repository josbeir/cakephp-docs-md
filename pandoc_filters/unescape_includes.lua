-- Post-process filter to fix escaped VitePress includes
-- Converts escaped HTML comments back to proper format

function Str(elem)
    local text = elem.text
    
    -- Fix escaped VitePress include comments
    text = text:gsub("\\<!%-%-@include:", "<!--@include:")
    text = text:gsub("%-%-\\>", "-->")
    
    if text ~= elem.text then
        return pandoc.Str(text)
    end
    
    return elem
end

function RawInline(elem)
    if elem.format == "html" then
        local text = elem.text
        
        -- Fix escaped VitePress include comments
        text = text:gsub("\\<!%-%-@include:", "<!--@include:")
        text = text:gsub("%-%-\\>", "-->")
        
        if text ~= elem.text then
            return pandoc.RawInline("html", text)
        end
    end
    
    return elem
end
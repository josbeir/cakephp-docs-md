-- Containers filter: Handle Sphinx container directives
-- Converts .. container:: directives to appropriate Markdown structures

function Div(div)
    local classes = div.attr.classes
    local id = div.attr.identifier
    
    -- Handle specific container types
    for _, class in ipairs(classes) do
        if class == "offline-download" then
            -- Convert download container to a simple section
            return pandoc.Div(div.content, pandoc.Attr(id, {"download-section"}, {}))
        elseif class:match("container") then
            -- Generic container handling - just preserve content
            return pandoc.Div(div.content, pandoc.Attr(id, {}, {}))
        end
    end
    
    return div
end

function RawBlock(elem)
    if elem.format == "rst" then
        local text = elem.text
        
        -- Handle container directives in raw RST
        if text:match("%.%. container::") then
            local container_type = text:match("%.%. container::%s*(.-)%s*$")
            
            -- Return a placeholder div that will be processed later
            if container_type and container_type ~= "" then
                return pandoc.Div({}, pandoc.Attr("", {container_type}, {}))
            else
                return pandoc.Div({}, pandoc.Attr("", {"container"}, {}))
            end
        end
    end
    
    return elem
end
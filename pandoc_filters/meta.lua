-- Meta filter: Extract Sphinx meta directives and convert to YAML front matter
-- Handles .. meta:: directives and converts them to Jekyll/VitePress compatible front matter

function Meta(meta)
    local frontmatter = {}
    
    for k, v in pairs(meta) do
        if k == "title" then
            frontmatter.title = pandoc.utils.stringify(v)
        elseif k == "keywords" then
            frontmatter.keywords = pandoc.utils.stringify(v)
        elseif k == "description" then
            frontmatter.description = pandoc.utils.stringify(v)
        end
    end
    
    return frontmatter
end

function Pandoc(doc)
    local frontmatter = {}
    local new_blocks = {}
    
    -- Process blocks to find meta directives
    for i, block in ipairs(doc.blocks) do
        if block.t == "RawBlock" and block.format == "rst" then
            local content = block.text
            
            -- Check if this is a meta directive block
            if content:match("%.%. meta::") then
                -- Extract title
                local title_match = content:match(":title lang=en:%s*(.-)%s*\n")
                if title_match then
                    frontmatter.title = title_match:gsub("^%.%. ", "")
                end
                
                -- Extract keywords
                local keywords_match = content:match(":keywords lang=en:%s*(.-)%s*$")
                if keywords_match then
                    frontmatter.keywords = keywords_match
                end
                
                -- Skip this block (don't add to new_blocks)
                goto continue
            end
        elseif block.t == "Div" and block.attr.classes[1] == "meta" then
            -- Handle div-style meta blocks, skip them
            goto continue
        end
        
        -- Keep non-meta blocks
        table.insert(new_blocks, block)
        ::continue::
    end
    
    -- Add frontmatter to metadata if found
    if next(frontmatter) then
        for k, v in pairs(frontmatter) do
            doc.meta[k] = pandoc.MetaString(v)
        end
    end
    
    -- Update document blocks
    doc.blocks = new_blocks
    
    return doc
end
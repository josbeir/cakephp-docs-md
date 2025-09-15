-- Includes filter: Convert RST include directives to VitePress markdown file inclusion
-- Converts .. include:: /path/file.rst to <!--@include: ./path/file.md-->
-- Supports :start-after:, :end-before:, :lines: and other RST include options

function RawBlock(elem)
    if elem.format == "rst" then
        local text = elem.text
        
        -- Check if this is an include directive
        if text:match("^%s*%.%.%s+include::") then
            local lines = {}
            for line in text:gmatch("[^\r\n]+") do
                table.insert(lines, line)
            end
            
            local include_path = nil
            local start_after = nil
            local end_before = nil
            local lines_arg = nil
            
            -- Parse the include directive and its options
            for i, line in ipairs(lines) do
                -- Parse the include path
                local path = line:match("^%s*%.%.%s+include::%s*(.+)$")
                if path then
                    include_path = path
                end
                
                -- Parse options
                local start_val = line:match("^%s*:start%-after:%s*(.+)$")
                if start_val then
                    start_after = start_val
                end
                
                local end_val = line:match("^%s*:end%-before:%s*(.+)$")
                if end_val then
                    end_before = end_val
                end
                
                local lines_val = line:match("^%s*:lines:%s*(.+)$")
                if lines_val then
                    lines_arg = lines_val
                end
            end
            
            if include_path then
                -- Convert .rst to .md
                include_path = include_path:gsub("%.rst$", ".md")
                
                -- Convert absolute paths to relative paths
                if include_path:match("^/") then
                    include_path = "." .. include_path
                end
                
                -- Build the VitePress include syntax
                local vitepress_include = "<!--@include: " .. include_path
                
                -- Handle line range arguments
                if lines_arg then
                    -- Convert RST lines syntax to VitePress format
                    -- RST: 3,5 -> VitePress: {3,5}
                    -- RST: 3- -> VitePress: {3,}
                    -- RST: -5 -> VitePress: {,5}
                    local line_range = lines_arg:gsub("%s", "") -- remove spaces
                    
                    if line_range:match("^%d+%-$") then
                        -- Format: "3-" -> "{3,}"
                        local start_line = line_range:match("^(%d+)%-$")
                        line_range = "{" .. start_line .. ",}"
                    elseif line_range:match("^%-%d+$") then
                        -- Format: "-5" -> "{,5}"
                        local end_line = line_range:match("^%-(%d+)$")
                        line_range = "{," .. end_line .. "}"
                    elseif line_range:match("^%d+,%d+$") then
                        -- Format: "3,5" -> "{3,5}"
                        line_range = "{" .. line_range .. "}"
                    elseif line_range:match("^%d+$") then
                        -- Format: "3" -> "{3}"
                        line_range = "{" .. line_range .. "}"
                    else
                        -- Default: assume it's already in correct format or wrap it
                        if not line_range:match("^{.*}$") then
                            line_range = "{" .. line_range .. "}"
                        end
                    end
                    
                    vitepress_include = vitepress_include .. line_range
                end
                
                vitepress_include = vitepress_include .. "-->"
                
                -- Add a comment about start-after/end-before if they exist
                -- (VitePress doesn't support these directly, so we add a comment)
                if start_after or end_before then
                    local comment_parts = {}
                    if start_after then
                        table.insert(comment_parts, "start-after: " .. start_after)
                    end
                    if end_before then
                        table.insert(comment_parts, "end-before: " .. end_before)
                    end
                    vitepress_include = vitepress_include .. "\n<!-- RST options: " .. table.concat(comment_parts, ", ") .. " -->"
                end
                
                return pandoc.RawBlock("markdown", vitepress_include)
            end
        end
    end
    
    return elem
end

function CodeBlock(elem)
    -- Handle include directives that might be in code blocks
    local text = elem.text
    
    if text:match("^%s*%.%.%s+include::") then
        local lines = {}
        for line in text:gmatch("[^\r\n]+") do
            table.insert(lines, line)
        end
        
        local include_path = nil
        local start_after = nil
        local end_before = nil
        local lines_arg = nil
        
        -- Parse the include directive and its options
        for i, line in ipairs(lines) do
            -- Parse the include path
            local path = line:match("^%s*%.%.%s+include::%s*(.+)$")
            if path then
                include_path = path
            end
            
            -- Parse options
            local start_val = line:match("^%s*:start%-after:%s*(.+)$")
            if start_val then
                start_after = start_val
            end
            
            local end_val = line:match("^%s*:end%-before:%s*(.+)$")
            if end_val then
                end_before = end_val
            end
            
            local lines_val = line:match("^%s*:lines:%s*(.+)$")
            if lines_val then
                lines_arg = lines_val
            end
        end
        
        if include_path then
            -- Convert .rst to .md
            include_path = include_path:gsub("%.rst$", ".md")
            
            -- Convert absolute paths to relative paths
            if include_path:match("^/") then
                include_path = "." .. include_path
            end
            
            -- Build the VitePress include syntax
            local vitepress_include = "<!--@include: " .. include_path
            
            -- Handle line range arguments
            if lines_arg then
                local line_range = lines_arg:gsub("%s", "") -- remove spaces
                
                if line_range:match("^%d+%-$") then
                    -- Format: "3-" -> "{3,}"
                    local start_line = line_range:match("^(%d+)%-$")
                    line_range = "{" .. start_line .. ",}"
                elseif line_range:match("^%-%d+$") then
                    -- Format: "-5" -> "{,5}"
                    local end_line = line_range:match("^%-(%d+)$")
                    line_range = "{," .. end_line .. "}"
                elseif line_range:match("^%d+,%d+$") then
                    -- Format: "3,5" -> "{3,5}"
                    line_range = "{" .. line_range .. "}"
                elseif line_range:match("^%d+$") then
                    -- Format: "3" -> "{3}"
                    line_range = "{" .. line_range .. "}"
                else
                    -- Default: assume it's already in correct format or wrap it
                    if not line_range:match("^{.*}$") then
                        line_range = "{" .. line_range .. "}"
                    end
                end
                
                vitepress_include = vitepress_include .. line_range
            end
            
            vitepress_include = vitepress_include .. "-->"
            
            -- Add a comment about start-after/end-before if they exist
            if start_after or end_before then
                local comment_parts = {}
                if start_after then
                    table.insert(comment_parts, "start-after: " .. start_after)
                end
                if end_before then
                    table.insert(comment_parts, "end-before: " .. end_before)
                end
                vitepress_include = vitepress_include .. "\n<!-- RST options: " .. table.concat(comment_parts, ", ") .. " -->"
            end
            
            return pandoc.RawBlock("markdown", vitepress_include)
        end
    end
    
    return elem
end
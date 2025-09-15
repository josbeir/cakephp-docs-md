-- PHP domain directive filter
-- Converts PHP Sphinx directives to formatted markdown

local current_namespace = ""
local current_class = ""

-- Function to extract namespace and class from a string
local function parse_fqdn(fqdn)
    local namespace, class = fqdn:match("^(.*)\\([^\\]+)$")
    if namespace and class then
        return namespace, class
    else
        return "", fqdn
    end
end

-- Function to format method signature
local function format_method(method_name, signature, full_class)
    if signature then
        -- Clean up the signature (remove extra spaces, normalize types)
        signature = signature:gsub("%s+", " "):gsub("^%s+", ""):gsub("%s+$", "")
        
        -- Format as: FullClassName::methodName(signature)
        return "`" .. full_class .. "::" .. "**" .. method_name .. "**(" .. signature .. ")`"
    else
        return "`" .. full_class .. "::" .. "**" .. method_name .. "**()`"
    end
end

-- Function to create class reference
local function format_class(class_name, namespace)
    if namespace and namespace ~= "" then
        local parts = {}
        for part in namespace:gmatch("[^\\]+") do
            table.insert(parts, part)
        end
        table.insert(parts, "**" .. class_name .. "**")
        return "`" .. table.concat(parts, "\\") .. "`"
    else
        return "`**" .. class_name .. "**`"
    end
end

function Div(elem)
    -- Handle php:namespace directive
    if elem.classes and elem.classes:includes("php-namespace") then
        -- Extract namespace from content
        for _, block in ipairs(elem.content) do
            if block.t == "Para" or block.t == "Plain" then
                for _, inline in ipairs(block.content) do
                    if inline.t == "Str" then
                        local namespace = inline.text:match("^([%w\\]+)$")
                        if namespace then
                            current_namespace = namespace
                            -- Return empty - we don't want to show the namespace directive itself
                            return {}
                        end
                    end
                end
            end
        end
        return {}
    end
    
    -- Handle php:class directive  
    if elem.classes and elem.classes:includes("php-class") then
        -- Extract class name from content
        for _, block in ipairs(elem.content) do
            if block.t == "Para" or block.t == "Plain" then
                for _, inline in ipairs(block.content) do
                    if inline.t == "Str" then
                        local class_name = inline.text:match("^(%w+)$")
                        if class_name then
                            current_class = class_name
                            -- Create formatted class reference
                            local formatted = format_class(class_name, current_namespace)
                            return pandoc.Para({pandoc.RawInline("markdown", formatted)})
                        end
                    end
                end
            end
        end
        return elem
    end
    
    -- Handle php:method directive
    if elem.classes and elem.classes:includes("php-method") then
        -- Extract method signature from content
        for _, block in ipairs(elem.content) do
            if block.t == "Para" or block.t == "Plain" then
                for _, inline in ipairs(block.content) do
                    if inline.t == "Str" then
                        local method_signature = inline.text
                        -- Parse method name and parameters
                        local method_name, params = method_signature:match("^(%w+)%((.*)%)$")
                        if method_name then
                            -- Build full class name
                            local full_class = current_namespace
                            if current_class and current_class ~= "" then
                                if full_class ~= "" then
                                    full_class = full_class .. "\\" .. current_class
                                else
                                    full_class = current_class
                                end
                            end
                            
                            local formatted = format_method(method_name, params, full_class)
                            return pandoc.Para({pandoc.RawInline("markdown", formatted)})
                        else
                            -- Try without parameters
                            method_name = method_signature:match("^(%w+)$")
                            if method_name then
                                local full_class = current_namespace
                                if current_class and current_class ~= "" then
                                    if full_class ~= "" then
                                        full_class = full_class .. "\\" .. current_class
                                    else
                                        full_class = current_class
                                    end
                                end
                                
                                local formatted = format_method(method_name, nil, full_class)
                                return pandoc.Para({pandoc.RawInline("markdown", formatted)})
                            end
                        end
                    end
                end
            end
        end
        return elem
    end
    
    return elem
end

-- Also handle inline PHP class references like :php:class:`ClassName`
function Link(elem)
    -- Check if this is a PHP class reference
    if elem.target:match("^php:class:") then
        local class_ref = elem.target:gsub("^php:class:", "")
        
        -- Handle different formats
        if class_ref:match("\\") then
            -- Full namespace provided
            local namespace, class = parse_fqdn(class_ref)
            return pandoc.Code(format_class(class, namespace):gsub("`", ""))
        else
            -- Just class name, use current context
            return pandoc.Code(format_class(class_ref, current_namespace):gsub("`", ""))
        end
    end
    
    return elem
end

-- Handle inline code that might be PHP references
function Code(elem)
    local text = elem.text
    
    -- Handle :php:class:`ClassName` patterns that might appear in code
    if text:match(":php:class:`[^`]+`") then
        local class_ref = text:match(":php:class:`([^`]+)`")
        if class_ref then
            if class_ref:match("\\") then
                local namespace, class = parse_fqdn(class_ref)
                elem.text = format_class(class, namespace):gsub("`", "")
            else
                elem.text = format_class(class_ref, current_namespace):gsub("`", "")
            end
        end
    end
    
    return elem
end
-- Version directives filter: Handle Sphinx versionadded, versionchanged, and deprecated directives
-- Converts versionadded, versionchanged, and deprecated divs to VitePress custom containers

function Div(div)
    local classes = div.attr.classes

    -- Check if this is a versionadded, versionchanged, or deprecated directive
    for _, class in ipairs(classes) do
        if class == "versionadded" or class == "versionchanged" or class == "deprecated" then
            -- Extract version and message from the div content
            local version = ""
            local message_parts = {}
            local found_version = false

            for _, block in ipairs(div.content) do
                if block.tag == "Para" then
                    for _, inline in ipairs(block.content) do
                        if inline.tag == "Str" then
                            local text = inline.text
                            -- Check if this looks like a version number
                            if not found_version and text:match("^[%d%.]+$") then
                                version = text
                                found_version = true
                            else
                                table.insert(message_parts, text)
                            end
                        elseif inline.tag == "Space" then
                            table.insert(message_parts, " ")
                        elseif inline.tag == "Code" then
                            table.insert(message_parts, "`" .. inline.text .. "`")
                        elseif inline.tag == "Link" then
                            -- Handle links: [text](url)
                            local link_text = pandoc.utils.stringify(inline.content)
                            local link_url = inline.target
                            table.insert(message_parts, "[" .. link_text .. "](" .. link_url .. ")")
                        elseif inline.tag == "Strong" then
                            -- Handle bold text: **text**
                            local strong_text = pandoc.utils.stringify(inline.content)
                            table.insert(message_parts, "**" .. strong_text .. "**")
                        elseif inline.tag == "Emph" then
                            -- Handle italic text: *text*
                            local emph_text = pandoc.utils.stringify(inline.content)
                            table.insert(message_parts, "*" .. emph_text .. "*")
                        else
                            -- Fallback for any other inline elements
                            local fallback_text = pandoc.utils.stringify({inline})
                            table.insert(message_parts, fallback_text)
                        end
                    end
                end
            end

            local message = table.concat(message_parts, ""):gsub("^%s+", ""):gsub("%s+$", "")

            -- Create the VitePress container with appropriate title
            local container_lines = {}
            local title
            if class == "versionadded" then
                title = "Added in version "
            elseif class == "versionchanged" then
                title = "Changed in version "
            elseif class == "deprecated" then
                title = "Deprecated in version "
            end
            table.insert(container_lines, "::: info " .. title .. version)

            if message ~= "" then
                table.insert(container_lines, message)
            end

            table.insert(container_lines, ":::")

            local container_text = table.concat(container_lines, "\n")

            -- Return as raw markdown
            return pandoc.RawBlock("markdown", container_text)
        end
    end

    return div
end
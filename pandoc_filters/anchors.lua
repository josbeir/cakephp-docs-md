-- Anchors filter: Convert RST anchor definitions to HTML anchor tags
-- Handles .. _anchor-name: patterns and inserts HTML anchors after headers

-- Get destination context and folder from environment
local destination_context = os.getenv("DESTINATION_CONTEXT") or ""
local destination_folder = os.getenv("DESTINATION_FOLDER") or ""

-- Cache to store anchors found in the current document
local document_anchors = {}
local anchors_processed = false

-- Function to extract anchors from the original RST content
local function extract_anchors_from_rst()
    if anchors_processed then
        return
    end

    -- Try to read the original RST file to extract anchors
    -- The convert script runs from legacy/en directory, so we need to account for that
    local rst_file = destination_context:gsub("%.md$", ".rst")

    -- Extract just the relative path from the output structure
    local folder_basename = destination_folder:match("([^/]+/[^/]+/[^/]+)$")
    local relative_rst_file = rst_file

    if folder_basename then
        local escaped_basename = folder_basename:gsub("[%-%.%+%[%]%(%)%$%^%%%?%*]", "%%%1")
        if relative_rst_file:match("^" .. escaped_basename .. "/") then
            relative_rst_file = relative_rst_file:gsub("^" .. escaped_basename .. "/", "")
        end
    end

    -- The convert script copies temp files, so we need to find the actual RST
    local possible_paths = {
        relative_rst_file,          -- Direct file (when running from legacy/en)
        rst_file,                   -- Full path
        rst_file:gsub("^[^/]+/", "legacy/en/"),
        "../" .. rst_file,
        "../../" .. rst_file
    }

    local content = nil
    for _, path in ipairs(possible_paths) do
        local file = io.open(path, "r")
        if file then
            content = file:read("*all")
            file:close()
            break
        end
    end

    if not content then
        -- Try to find the specific RST file in current directory (avoid temp files)
        -- The convert script creates temp files, so only look for the exact file we need
        local base_name = relative_rst_file:match("([^/]+)$")
        if base_name then
            local file = io.open(base_name, "r")
            if file then
                content = file:read("*all")
                file:close()
            end
        end
    end

    if content then
        -- Extract anchors and their positions relative to headers
        local lines = {}
        for line in content:gmatch("[^\r\n]+") do
            table.insert(lines, line)
        end

        local current_anchor = nil
        for i, line in ipairs(lines) do
            -- Check for anchor definition
            local anchor = line:match("^%.%. _([^:]+):")
            if anchor then
                current_anchor = anchor
            elseif current_anchor and line:match("^[^%s]") then
                -- This is likely a header line (non-indented after anchor)
                -- Check if next line is header underline
                if i + 1 <= #lines then
                    local next_line = lines[i + 1]
                    if next_line:match("^[=#*+^~-]+$") and #next_line >= #line then
                        -- This is a header, associate the anchor with it
                        local header_text = line:gsub("^%s+", ""):gsub("%s+$", "")
                        document_anchors[header_text] = current_anchor
                    end
                end
                current_anchor = nil
            end
        end
    end

    anchors_processed = true
end

-- Function to create HTML anchor tag
local function create_anchor_tag(anchor_id)
    return pandoc.RawBlock("html", '<a id="' .. anchor_id .. '"></a>')
end

-- Process the entire document to handle anchors
function Pandoc(doc)
    -- Extract anchors before processing
    extract_anchors_from_rst()

    -- Process all blocks in the document
    local new_blocks = {}

    for i, block in ipairs(doc.blocks) do
        if block.t == "Header" then
            local header_text = pandoc.utils.stringify(block)
            local anchor_id = document_anchors[header_text]

            if anchor_id then
                -- Add anchor before header
                table.insert(new_blocks, create_anchor_tag(anchor_id))
                table.insert(new_blocks, block)
            else
                table.insert(new_blocks, block)
            end
        else
            table.insert(new_blocks, block)
        end
    end

    return pandoc.Pandoc(new_blocks, doc.meta)
end
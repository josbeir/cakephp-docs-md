-- Image path normalization filter
-- Converts /_static/img/ paths to root /

function Image(elem)
    -- Check if the image source starts with /_static/img/
    if elem.src:match("^/_static/img/") then
        -- Replace /_static/img/ with just /
        elem.src = elem.src:gsub("^/_static/img/", "/")
    end
    
    -- Also normalize paths in alt text
    if elem.attributes and elem.attributes.alt then
        elem.attributes.alt = elem.attributes.alt:gsub("/_static/img/", "/")
    end
    
    return elem
end
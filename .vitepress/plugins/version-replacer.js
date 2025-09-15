/**
 * Markdown-it plugin for replacing version placeholders
 * 
 * Replaces |phpversion| and |minphpversion| with actual PHP version requirements
 * based on the CakePHP version being rendered.
 */

import { getVersionByPath } from '../cake.js'

/**
 * Create a version replacer plugin
 * @param {Object} md - markdown-it instance
 * @param {Object} options - plugin options
 * @returns {void}
 */
export function versionReplacer(md, options = {}) {
  // Store original render method
  const originalRender = md.render.bind(md)
  
  // Override render method to inject version replacement
  md.render = function(src, env = {}) {
    // Get version info from the current file path
    const versionInfo = getVersionByPath(env.relativePath || '')
    
    // Replace version placeholders with actual values
    if (versionInfo) {
      src = src
        .replace(/\|phpversion\|/g, `**${versionInfo.phpVersion || '8.1'}**`)
        .replace(/\|minphpversion\|/g, `*${versionInfo.minPhpVersion || '8.1'}*`)
    }
    
    // Call original render with processed source
    return originalRender(src, env)
  }
}

export default versionReplacer
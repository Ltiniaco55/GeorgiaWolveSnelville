/**
 * SVGO config for the brand SVGs.
 *
 * Usage (from the project root):
 *   vendor/bin/sail npx --yes svgo@4 --config resources/svg/svgo.config.mjs -f resources/svg/raw -o resources/svg
 *
 * - Keeps the viewBox and the geometry: paths are only re-encoded, never merged, and
 *   transforms are not baked into the coordinates (that rounds the favicon's paths).
 * - Removes <metadata> (including the C2PA manifest), the unused c2pa namespace and leftover ids.
 * - Removes width/height so the SVGs scale with CSS when inlined.
 * - Removes the root inline `style="color:…"` of the monochrome files so `currentColor`
 *   inherits the CSS color (BrandLogo applies each variant's default brand color).
 */
export default {
    multipass: true,
    plugins: [
        {
            name: 'preset-default',
            params: {
                overrides: {
                    mergePaths: false,
                    moveGroupAttrsToElems: false,
                    convertTransform: false,
                    convertPathData: { applyTransforms: false },
                },
            },
        },
        'removeDimensions',
        {
            name: 'removeAttrs',
            params: { attrs: 'svg:style' },
        },
    ],
};

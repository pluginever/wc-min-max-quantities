const path = require('path');

module.exports = ({file, env}) => {
    const config = {
        plugins: {
            autoprefixer: {grid: true},
            cssnano: env === 'production',
        },
    };


    // Only load postcss-editor-styles plugin when we're processing the editor-style.css file.
    if (path.basename(file) === 'editor-style.scss') {
        config.plugins['postcss-editor-styles'] = {
            scopeTo: '.editor-styles-wrapper',
            ignore: [':root', '.edit-post-visual-editor.editor-styles-wrapper', '.wp-toolbar'],
            remove: ['html', ':disabled', '[readonly]', '[disabled]'],
            tags: ['button', 'input', 'label', 'select', 'textarea', 'form', 'p'],
        };
    }


    return config;
}
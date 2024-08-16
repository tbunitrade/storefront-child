(function(blocks, element) {
    var el = element.createElement;

    blocks.registerBlockType('storefront-child/city-weather', {
        title: 'City Weather',
        icon: 'cloud',
        category: 'widgets',
        edit: function() {
            return el('p', {}, 'City Weather Block');
        },
        save: function() {
            return null; // Используем динамическое отображение через PHP
        }
    });
})(
    window.wp.blocks,
    window.wp.element
);
console.log('block load ok');
$(function()
{
    var update_fields = function( root )
    {
        var fields = {};
        $( '.meta .fields li[rel]', root )
            .each
            (
                function( index, element )
                {
                    element = $( element );
                    fields[element.attr('rel')] = element.css( 'height', 'auto' ).height();
                }
            );
        
        $( '.results .row', root )
            .each
            (
                function( index, element )
                {
                    for( var field in fields )
                    {
                        fields[field] = Math.max
                        (
                            fields[field],
                            $( 'li[rel="' + field + '"]', element ).css( 'height', 'auto' ).height()
                        );
                    }
                }
            );
        
        for( var field in fields )
        {
            $( 'li[rel="' + field + '"]', root )
                .height( fields[field] );
        }
    }

    var clear_rows = function( root )
    {
        $( '.results > ul', root )
            .css( 'width', 'auto' );
    }

    var update_rows = function( root )
    {
        var container = $( '.results > ul', root );

        var width = 0;
        $( '.row', container )
            .each
            (
                function( index, row )
                {
                    width += $( row ).width();
                    width += 1; // border
                    width += 5; // padding
                    width += 5; // margin
                }
            );
        
        container.width( width );
    }

    var update_screen = function( root )
    {
        clear_rows( root );
        update_fields( root );
        update_rows( root );
    }

    var init_screen = function()
    {
        $( '.root' )
            .each
            (
                function( index, root )
                {
                    update_screen( root );
                }
            );
    }
    init_screen();

    $( '.results .row > a' )
        .die( 'click' )
        .bind
        (
            'click',
            function( event )
            {
                $( this ).closest( '.row' )
                    .toggleClass( 'show' );

                update_screen( $( this ).closest( '.root' ) );

                return false;
            }
        )
    
    $( '.root' )
        .each
        (
            function( index, root )
            {
                $( '.results .row > a', root )
                    .first().trigger( 'click' );
            }
        );
});
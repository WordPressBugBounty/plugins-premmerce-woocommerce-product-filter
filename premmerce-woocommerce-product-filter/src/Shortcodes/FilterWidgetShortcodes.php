<?php

namespace Premmerce\Filter\Shortcodes;

use  Premmerce\Filter\FilterPlugin ;
use  Premmerce\Filter\Widget\FilterWidget ;
use  Premmerce\SDK\V2\FileManager\FileManager ;
use  Premmerce\Filter\Widget\ActiveFilterWidget ;
class FilterWidgetShortcodes
{
    /**
     * File Manager
     *
     * @var FileManager
     */
    private  $fileManager ;
    /**
     * Shortcode constructor
     */
    public function __construct( FileManager $fileManager )
    {
        $this->fileManager = $fileManager;
    }
    
    /**
     * Shortcode data and render template
     */
    public function premmerceShortcodeFilter( $atts, $type = 'shortcode', $isRender = false )
    {
        //check if it is woocomerce pages and we can show this shortcode
        
        if ( apply_filters( 'premmerce_product_filter_active', false ) || $isRender ) {
            if ( empty($atts) ) {
                $atts = array();
            }
            $type = ( !empty($type) ? $type : 'shortcode' );
            $getId = get_the_ID();
            $uniqId = uniqid();
            $args['id'] = "{$type}-{$getId}-{$uniqId}";
            $args['name'] = $type;
            if ( empty($atts['style']) ) {
                $atts['style'] = 'custom';
            }
            //take data from FilterWidget class
            $data = FilterWidget::getFilterWidgetContent( $args, $atts );
            //render filter
            return $this->fileManager->renderTemplate( 'frontend/filter.php', $data );
        }
    
    }
    
    /**
     * Shortcode Instruction
     *
     * @return void
     */
    public static function shortcodeInstruction()
    {
        $instruction = '';
        //attributes info
        $attrList = array(
            'style'                 => array(
            'example_data' => 'custom',
            'description'  => __( 'Filter style. Can be <code>default</code>, <code>premmerce</code>, <code>custom</code>. ', 'premmerce-filter' ) . '<br>' . __( 'All other attributes you can use after adding <code>style="custom"</code> attribute.', 'premmerce-filter' ),
        ),
            'bg_color'              => array(
            'example_data' => '#fff',
            'description'  => __( 'Filter Background color', 'premmerce-filter' ),
        ),
            'add_border'            => array(
            'example_data' => 'on',
            'description'  => __( 'Filter Border', 'premmerce-filter' ),
        ),
            'border_color'          => array(
            'example_data' => '#000',
            'description'  => __( 'Filter Border Color', 'premmerce-filter' ),
        ),
            'bold_title'            => array(
            'example_data' => 'on',
            'description'  => __( 'Make filter title bold', 'premmerce-filter' ),
        ),
            'title_appearance'      => array(
            'example_data' => 'uppercase',
            'description'  => __( 'Make title text <code>uppercase</code> or <code>default</code>', 'premmerce-filter' ),
        ),
            'price_input_bg'        => array(
            'example_data' => '#fff',
            'description'  => __( 'Filter Price Input Background', 'premmerce-filter' ),
        ),
            'price_input_text'      => array(
            'example_data' => '#000',
            'description'  => __( 'Filter Price Input Color', 'premmerce-filter' ),
        ),
            'price_slider_range'    => array(
            'example_data' => '#000',
            'description'  => __( 'Filter Price Slider Range Color', 'premmerce-filter' ),
        ),
            'price_slider_handle'   => array(
            'example_data' => '#000',
            'description'  => __( 'Filter Price Slider Handle Color', 'premmerce-filter' ),
        ),
            'checkbox_appearance'   => array(
            'example_data' => '0',
            'description'  => __( 'Choose Checkbox Appearance: ', 'premmerce-filter' ) . '<br><code>0</code> : BALLOT BOX, ' . '<code>2713</code> : BALLOT BOX WITH CHECK, ' . '<code>2715</code> : BALLOT BOX WITH X',
        ),
            'title_size'            => array(
            'example_data' => '14',
            'description'  => __( 'Titles Font Size', 'premmerce-filter' ),
        ),
            'title_color'           => array(
            'example_data' => '#000',
            'description'  => __( 'Titles Color', 'premmerce-filter' ),
        ),
            'terms_title_size'      => array(
            'example_data' => '14',
            'description'  => __( 'Terms Titles Font Size', 'premmerce-filter' ),
        ),
            'terms_title_color'     => array(
            'example_data' => '#000',
            'description'  => __( 'Terms Titles Color', 'premmerce-filter' ),
        ),
            'checkbox_color'        => array(
            'example_data' => '#000',
            'description'  => __( 'Checkbox/Radio Color', 'premmerce-filter' ),
        ),
            'checkbox_border_color' => array(
            'example_data' => '#000',
            'description'  => __( 'Checkbox/Radio Border Color', 'premmerce-filter' ),
        ),
        );
        //filter shortcode
        $instruction .= '<h3>' . __( 'Filter Shortcode', 'premmerce-filter' ) . '</h3>';
        $instruction .= '<div class="premmerce-shortcode-example">[premmerce_filter';
        $i = 0;
        foreach ( $attrList as $key => $attr ) {
            $i++;
            $instruction .= " {$key}=\"{$attr['example_data']}\"";
            if ( 5 === $i ) {
                break;
            }
        }
        $instruction .= ']</div>';
        //filter shortcode with all attributes
        $instruction .= '<h3>' . __( 'Filter Shortcode with all attributes', 'premmerce-filter' ) . '</h3>';
        $instruction .= '<div class="premmerce-shortcode-example premmerce-shortcode-all-attr">[premmerce_filter';
        foreach ( $attrList as $key => $attr ) {
            $instruction .= " {$key}=\"{$attr['example_data']}\"";
        }
        $instruction .= ']</div>';
        //Attributes Description
        $instruction .= '<h3>' . __( 'Filter Shortcode Attributes:', 'premmerce-filter' ) . '</h3>';
        $instruction .= '<table class="premmerce-shortcodes-attr-desc">';
        foreach ( $attrList as $key => $attr ) {
            $instruction .= "<tr><td class='premmerce-shortcode-attr'>{$key}=\"{$attr['example_data']}\"</td><td>{$attr['description']}</td><tr>";
        }
        $instruction .= '</table>';
        return $instruction;
    }

}
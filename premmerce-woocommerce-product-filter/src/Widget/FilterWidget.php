<?php

namespace Premmerce\Filter\Widget;

use WP_Widget;
use Premmerce\Filter\FilterPlugin;
use Premmerce\Filter\Filter\Container;
class FilterWidget extends WP_Widget {
    const FILTER_WIDGET_ID = 'premmerce_filter_filter_widget';

    /**
     * Settings the filter widget, the [premmerce_filter] shortcode and the block take, and how
     * each is cleaned: text, one of a list of values, on or off, a CSS colour or a size in pixels.
     * They end up in the filter's markup and its inline CSS.
     */
    const STYLE_SETTINGS = array(
        'title'                 => 'text',
        'style'                 => array('default', 'premmerce', 'custom'),
        'add_border'            => 'toggle',
        'border_color'          => 'color',
        'price_input_bg'        => 'color',
        'price_input_text'      => 'color',
        'price_slider_range'    => 'color',
        'price_slider_handle'   => 'color',
        'checkbox_appearance'   => array('0', '2713', '2715'),
        'title_appearance'      => array('default', 'uppercase'),
        'bold_title'            => 'toggle',
        'bg_color'              => 'color',
        'title_size'            => 'size',
        'title_color'           => 'color',
        'terms_title_size'      => 'size',
        'terms_title_color'     => 'color',
        'checkbox_border_color' => 'color',
        'checkbox_color'        => 'color',
    );

    /**
     * FilterWidget constructor.
     */
    public function __construct() {
        parent::__construct( self::FILTER_WIDGET_ID, __( 'Premmerce Filter', 'premmerce-filter' ), array(
            'description' => __( 'Product attributes filter', 'premmerce-filter' ),
        ) );
    }

    /**
     * Render widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        if ( apply_filters( 'premmerce_product_filter_active', false ) ) {
            $data = self::getFilterWidgetContent( $args, $instance );
            do_action( 'premmerce_product_filter_render', $data );
        }
    }

    /**
     * Get Filter Widget data
     *
     * @param array $args
     * @param array $instance
     */
    public static function getFilterWidgetContent( $args = array(), $instance = array() ) {
        $items = Container::getInstance()->getItemsManager()->getFilters();
        $items = apply_filters( 'premmerce_product_filter_items', $items );
        $settings = get_option( FilterPlugin::OPTION_SETTINGS, array() );
        $style = ( isset( $instance['style'] ) ? $instance['style'] : 'default' );
        $showFilterButton = !empty( $settings['show_filter_button'] );
        //default styles
        $border = '';
        $boldTitle = '';
        $titleAppearance = '';
        //premmerce styles
        if ( 'default' !== $style ) {
            //border styles
            if ( isset( $instance['add_border'] ) && ('on' === $instance['add_border'] || true === $instance['add_border']) || 'premmerce' === $style ) {
                $border = ' filter__item-border';
            }
            //title styles
            if ( isset( $instance['bold_title'] ) && ('on' === $instance['bold_title'] || true === $instance['bold_title']) || 'premmerce' === $style ) {
                $boldTitle = 'bold';
            }
            if ( isset( $instance['title_appearance'] ) && 'uppercase' === $instance['title_appearance'] || 'premmerce' === $style ) {
                $titleAppearance = 'uppercase';
            }
        }
        $data = array(
            'args'             => $args,
            'style'            => $style,
            'showFilterButton' => $showFilterButton,
            'attributes'       => $items,
            'formAction'       => apply_filters( 'premmerce_product_filter_form_action', '' ),
            'instance'         => $instance,
            'border'           => $border,
            'boldTitle'        => $boldTitle,
            'titleAppearance'  => $titleAppearance,
        );
        return $data;
    }

    /**
     * Keep only the style settings, each cleaned. Settings that aren't set stay unset, so the
     * filter's defaults apply to them as before.
     *
     * @param array $settings Widget instance, shortcode attributes or block attributes.
     *
     * @return array
     */
    public static function sanitizeStyleSettings( $settings ) {
        $clean = array();
        if ( !is_array( $settings ) ) {
            return $clean;
        }
        foreach ( array_keys( self::STYLE_SETTINGS ) as $key ) {
            if ( isset( $settings[$key] ) ) {
                $clean[$key] = self::sanitizeStyleSetting( $key, $settings[$key] );
            }
        }
        return $clean;
    }

    /**
     * Clean one style setting. A value that doesn't fit becomes empty, or the first of a list.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return string
     */
    public static function sanitizeStyleSetting( $key, $value ) {
        $settings = self::STYLE_SETTINGS;
        $rule = ( isset( $settings[$key] ) ? $settings[$key] : 'text' );
        //on/off: a checkbox or shortcode sends 'on', the block true
        if ( 'toggle' === $rule ) {
            return ( 'on' === $value || true === $value ? 'on' : '' );
        }
        $value = ( is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '' );
        if ( is_array( $rule ) ) {
            $value = strtolower( $value );
            return ( in_array( $value, $rule, true ) ? $value : $rule[0] );
        }
        switch ( $rule ) {
            case 'color':
                //a hex, rgb(), hsl() or named colour: nothing that can end the CSS declaration
                return ( preg_match( '/^[#a-zA-Z0-9(),.%\\s-]*$/', $value ) ? $value : '' );
            case 'size':
                return ( is_numeric( $value ) ? (string) abs( (float) $value ) : '' );
            default:
                return $value;
        }
    }

    /**
     * Update
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = self::sanitizeStyleSetting( 'title', $new_instance['title'] );
        $instance['style'] = self::sanitizeStyleSetting( 'style', $new_instance['style'] );
        return $instance;
    }

    /**
     * Form
     *
     * @param array $instance
     *
     * @return string|void
     */
    public function form( $instance ) {
        $settings = get_option( FilterPlugin::OPTION_SETTINGS, array() );
        //check plan
        $premiumOnly = ( !premmerce_pwpf_fs()->can_use_premium_code() ? __( ' (Premium)', 'premmerce-filter' ) : '' );
        $currentPlan = ( !premmerce_pwpf_fs()->can_use_premium_code() ? FilterPlugin::PLAN_FREE : FilterPlugin::PLAN_PREMIUM );
        //options from settings page
        $filterStyles = array(
            'default'   => __( 'Default', 'premmerce-filter' ),
            'premmerce' => 'Premmerce',
        );
        //add custom option
        $filterStyles['custom'] = 'Custom' . $premiumOnly;
        //default variables
        $checkboxAppVariables = array(
            '0'    => 'BALLOT BOX',
            '2713' => 'BALLOT BOX WITH CHECK',
            '2715' => 'BALLOT BOX WITH X',
        );
        $titleAppVariables = array(
            'default'   => 'Default',
            'uppercase' => 'Uppercase',
        );
        do_action( 'premmerce_product_filter_widget_form_render', array(
            'settings'             => $settings,
            'title'                => ( isset( $instance['title'] ) ? $instance['title'] : '' ),
            'filterStyles'         => $filterStyles,
            'style'                => ( isset( $instance['style'] ) ? $instance['style'] : '' ),
            'addBorder'            => ( isset( $instance['add_border'] ) ? $instance['add_border'] : 'on' ),
            'borderColor'          => ( isset( $instance['border_color'] ) ? $instance['border_color'] : '' ),
            'priceInputBg'         => ( isset( $instance['price_input_bg'] ) ? $instance['price_input_bg'] : '' ),
            'priceInputText'       => ( isset( $instance['price_input_text'] ) ? $instance['price_input_text'] : '' ),
            'priceSliderRange'     => ( isset( $instance['price_slider_range'] ) ? $instance['price_slider_range'] : '' ),
            'priceSliderHandle'    => ( isset( $instance['price_slider_handle'] ) ? $instance['price_slider_handle'] : '' ),
            'checkboxAppVariables' => $checkboxAppVariables,
            'checkboxAppearance'   => ( isset( $instance['checkbox_appearance'] ) ? $instance['checkbox_appearance'] : '' ),
            'titleAppVariables'    => $titleAppVariables,
            'titleAppearance'      => ( isset( $instance['title_appearance'] ) ? $instance['title_appearance'] : 'default' ),
            'boldTitle'            => ( isset( $instance['bold_title'] ) ? $instance['bold_title'] : '' ),
            'titleSize'            => ( isset( $instance['title_size'] ) ? $instance['title_size'] : '' ),
            'titleColor'           => ( isset( $instance['title_color'] ) ? $instance['title_color'] : '' ),
            'termsTitleSize'       => ( isset( $instance['terms_title_size'] ) ? $instance['terms_title_size'] : '' ),
            'termsTitleColor'      => ( isset( $instance['terms_title_color'] ) ? $instance['terms_title_color'] : '' ),
            'bgColor'              => ( isset( $instance['bg_color'] ) ? $instance['bg_color'] : '' ),
            'checkboxColor'        => ( isset( $instance['checkbox_color'] ) ? $instance['checkbox_color'] : '' ),
            'checkboxBorderColor'  => ( isset( $instance['checkbox_border_color'] ) ? $instance['checkbox_border_color'] : '' ),
            'currentPlan'          => $currentPlan,
            'widget'               => $this,
        ) );
    }

    /**
     * Render ColorPicker for widget
     */
    public static function renderWidgetInput(
        $widget,
        $id,
        $title,
        $value,
        $class,
        $type = 'text',
        $plan = 'premium'
    ) {
        $checkbox = '<p><label for="%1$s">%2$s</label><input class="widefat %3$s %4$s" type="%5$s" name="%6$s" id="%7$s" value="%8$s" %9$s></p>';
        $fieldID = esc_attr( $widget->get_field_id( $id ) );
        $disabled = '';
        //if it is not premium plan - disable input
        if ( FilterPlugin::PLAN_FREE === $plan ) {
            $disabled = 'disabled';
        }
        printf(
            '<p><label for="%1$s">%2$s</label><input class="widefat %3$s %4$s" type="%5$s" name="%6$s" id="%7$s" value="%8$s" %9$s></p>',
            esc_attr( $fieldID ),
            esc_attr( $title ),
            esc_attr( $class ),
            esc_attr( $disabled ),
            esc_attr( $type ),
            esc_attr( $widget->get_field_name( $id ) ),
            esc_attr( $fieldID ),
            esc_attr( $value ),
            esc_attr( $disabled )
        );
    }

    /**
     * Render checkbox for widget
     */
    public static function renderWidgetCheckbox(
        $widget,
        $id,
        $title,
        $value,
        $plan
    ) {
        $checked = checked( $value, 'on', false );
        $fieldID = esc_attr( $widget->get_field_id( $id ) );
        $disabled = '';
        //if it is not premium plan - disable checkbox
        if ( FilterPlugin::PLAN_FREE === $plan ) {
            $disabled = 'disabled';
        }
        printf(
            '<p><input class="widefat" type="checkbox" name="%1$s" id="%2$s" %3$s %4$s><label for="%5$s">%6$s</label></p>',
            esc_attr( $widget->get_field_name( $id ) ),
            esc_attr( $fieldID ),
            esc_attr( $checked ),
            esc_attr( $disabled ),
            esc_attr( $fieldID ),
            esc_attr( $title )
        );
    }

    /**
     * Render select for widget
     */
    public static function renderWidgetSelect(
        $widget,
        $id,
        $title,
        $value,
        $options,
        $class = '',
        $plan = 'premium'
    ) {
        $fieldID = esc_attr( $widget->get_field_id( $id ) );
        $disabled = '';
        //if it is not premium plan - disable select
        if ( FilterPlugin::PLAN_FREE === $plan ) {
            $disabled = 'disabled';
        }
        printf(
            '<p><label for="%1$s">%2$s</label><select name="%3$s" class="widefat %4$s" id="%5$s" %6$s></p>',
            esc_attr( $fieldID ),
            esc_attr( $title ),
            esc_attr( $widget->get_field_name( $id ) ),
            esc_attr( $class ),
            esc_attr( $fieldID ),
            esc_attr( $disabled )
        );
        foreach ( $options as $key => $option ) {
            $selected = ( $key === $value ? 'selected' : '' );
            printf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr( $key ),
                esc_attr( $selected ),
                esc_attr( $option )
            );
        }
        print '</select>';
    }

}

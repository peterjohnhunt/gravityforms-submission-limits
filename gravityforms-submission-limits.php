<?php
/*
 * Plugin Name: Gravity Forms Submission Limit
 * Version: 1.0.0
 * Plugin URI: https://www.usefulgroup.com
 * Description: Plugin to allow limiting form submission people
 * Author: Useful Group
 * Author URI: https://www.usefulgroup.com
 * Text Domain: gravityforms-submission-limit
 */

//≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡
// ✅ Helpers
//≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡
function gfsl_people_limit_settings_html($form) {
    $subsetting_open  = '
        <td colspan="2" class="gf_sub_settings_cell">
            <div class="gf_animate_sub_settings">
                <table>
                    <tr>';

    $subsetting_close = '
                    </tr>
                </table>
            </div>
        </td>';
    
    // Limit people.
    $limit_people_checked = '';
    $limit_people_style   = '';
    $limit_people_dd    = '';
    if ( rgar( $form, 'limitPeople' ) ) {
        $limit_people_checked = 'checked="checked"';

    } else {
        $limit_people_style = 'display:none';
    }

    $limit_periods = array(
        ''      => __( 'total people', 'gravityforms' ),
        'day'   => __( 'per day', 'gravityforms' ),
        'week'  => __( 'per week', 'gravityforms' ),
        'month' => __( 'per month', 'gravityforms' ),
        'year'  => __( 'per year', 'gravityforms' )
    );
    foreach ( $limit_periods as $value => $label ) {
        $selected = rgar( $form, 'limitPeoplePeriod' ) == $value ? 'selected="selected"' : '';
        $limit_people_dd .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
    }

    $tr_limit_people = '
    <script>
        function ToggleLimitPeople() {
            if (jQuery("#gform_limit_people").is(":checked")) {
                ShowSettingRow("#limit_people_count_setting");
                ShowSettingRow("#limit_people_message_setting");
                ShowSettingRow("#limit_people_admin_label_setting");
            }
            else {
                HideSettingRow("#limit_people_count_setting");
                HideSettingRow("#limit_people_message_setting");
                HideSettingRow("#limit_people_admin_label_setting");
            }
        }
    </script>
    <tr>
        <th>
            ' . __( 'Limit number of people', 'gravityforms' ) . ' ' . gform_tooltip( 'form_limit_people', '', true ) . '
        </th>
        <td>
            <input type="checkbox" id="gform_limit_people" name="form_limit_people" onclick="ToggleLimitPeople();" onkeypress="ToggleLimitPeople();" value="1" ' . $limit_people_checked . ' />
            <label for="gform_limit_people">' . __( 'Enable people limit', 'gravityforms' ) . '</label>
        </td>
    </tr>';

    // Limit people count.
    $tr_limit_people_count = '
    <tr id="limit_people_count_setting" class="child_setting_row" style="' . esc_attr( $limit_people_style ) . '">
        ' . $subsetting_open . '
        <th>
            ' .
        __( 'Number of People', 'gravityforms' ) .
        '
    </th>
    <td>
        <input type="text" id="gform_limit_people_count" name="form_limit_people_count" style="width:70px;" value="' . esc_attr( rgar( $form, 'limitPeopleCount' ) ) . '" />
            &nbsp;
            <select id="gform_limit_people_period" name="form_limit_people_period" style="height:22px;">' .
        $limit_people_dd .
        '</select>
    </td>
    ' . $subsetting_close . '
    </tr>';

    // Limit people message.
    $tr_limit_people_message = '
    <tr id="limit_people_message_setting" class="child_setting_row" style="' . $limit_people_style . '">
        ' . $subsetting_open . '
        <th>
            <label for="form_limit_people_message">' .
        __( 'People Limit Reached Message', 'gravityforms' ) .
        '</label>
    </th>
    <td>
        <textarea id="form_limit_people_message" name="form_limit_people_message" class="fieldwidth-3">' . esc_html( rgar( $form, 'limitPeopleMessage' ) ) . '</textarea>
        </td>
        ' . $subsetting_close . '
    </tr>
    ';

    // Limit people admin label.
    $tr_limit_people_admin_label = '
    <tr id="limit_people_admin_label_setting" class="child_setting_row" style="' . $limit_people_style . '">
        ' . $subsetting_open . '
        <th>
            <label for="form_limit_people_admin_label">' .
        __( 'People Limit Field Admin Label', 'gravityforms' ) .
        '</label>
    </th>
    <td>
        <input id="form_limit_people_admin_label" name="form_limit_people_admin_label" class="fieldwidth-3" value="' . esc_html( rgar( $form, 'limitPeopleAdminLabel' ) ) . '">
        </td>
        ' . $subsetting_close . '
    </tr>
    ';

    return ['limit_people' => $tr_limit_people, 'number_of_people' => $tr_limit_people_count, 'people_limit_message' => $tr_limit_people_message, 'people_limit_admin_label' => $tr_limit_people_admin_label];
}


function gfsl_get_limit_period_dates( $period ) {
    if ( empty( $period ) ) {
        return array( 'start_date' => null, 'end_date' => null );
    }

    switch ( $period ) {
        case 'day':
            return array(
                'start_date' => current_time( 'Y-m-d' ),
                'end_date'   => current_time( 'Y-m-d 23:59:59' ),
            );
            break;

        case 'week':
            return array(
                'start_date' => gmdate( 'Y-m-d', strtotime( 'Monday this week' ) ),
                'end_date'   => gmdate( 'Y-m-d 23:59:59', strtotime( 'next Sunday' ) ),
            );
            break;

        case 'month':
            $month_start = gmdate( 'Y-m-1');
            return array(
                'start_date' => $month_start,
                'end_date'   => gmdate( 'Y-m-d H:i:s', strtotime( "{$month_start} +1 month -1 second" ) ),
            );
            break;

        case 'year':
            return array(
                'start_date' => gmdate( 'Y-1-1' ),
                'end_date'   => gmdate( 'Y-12-31 23:59:59' ),
            );
            break;
    }
}

function gfsl_get_count_fields( $form ) {
    $admin_label = rgar( $form, 'limitPeopleAdminLabel' );

    if ( !$admin_label ) return;

    $fields = array_filter($form['fields'], function($f) use ($admin_label){ return $f->adminLabel == $admin_label; });

    return $fields;
}

function gfsl_get_people_count( $form ) {
    $count           = 0;
    $fields          = gfsl_get_count_fields($form);
    $field_ids       = wp_list_pluck($fields, 'id');
    $period          = rgar( $form, 'limitPeoplePeriod' );
    $range           = gfsl_get_limit_period_dates( $period );
    $search_criteria = array(
        'status'     => 'active',
        'start_date' => $range['start_date'],
        'end_date'   => $range['end_date'],
    );
    
    $entries = GFAPI::get_entries($form['id'], $search_criteria);
    
    if ( !$entries ) return $count;
    
    foreach ($entries as $entry) {
        foreach ($field_ids as $field_id) {
            $count += intval(rgar( $entry, $field_id )) ?: 0;
        }
    }

    return $count;
}

//≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡
// ✅ Settings
//≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡
function gfsl_add_people_limit_settings($settings, $form) {
    $settings[ __( 'Restrictions', 'gravityforms' ) ] = array_merge($settings[ __( 'Restrictions', 'gravityforms' ) ], gfsl_people_limit_settings_html($form));

    return $settings;
}
add_filter( 'gform_form_settings', 'gfsl_add_people_limit_settings', 10, 2 );

function gfsl_add_people_limit_tooltip( $tooltips ) {
    $tooltips['form_limit_people'] = '<h6>' . __( 'Limit Number of People', 'gravityforms' ) . '</h6>' . __( 'Enter a number in the input box below to limit the number of people allowed for this form. The form will become inactive when that number is reached.', 'gravityforms' );
    return $tooltips;
}
add_filter( 'gform_tooltips', 'gfsl_add_people_limit_tooltip' );

function gfsl_save_people_settings( $updated_form ) {
    $updated_form['limitPeople']           = rgpost( 'form_limit_people' );
    $updated_form['limitPeopleCount']      = $updated_form['limitPeople'] ? rgpost( 'form_limit_people_count' ) : '';
    $updated_form['limitPeoplePeriod']     = $updated_form['limitPeople'] ? rgpost( 'form_limit_people_period' ) : '';
    $updated_form['limitPeopleMessage']    = $updated_form['limitPeople'] ? rgpost( 'form_limit_people_message' ) : '';
    $updated_form['limitPeopleAdminLabel'] = $updated_form['limitPeople'] ? rgpost( 'form_limit_people_admin_label' ) : '';

    return $updated_form;
}
add_filter( 'gform_pre_form_settings_save', 'gfsl_save_people_settings' );


//≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡
// ✅ Validation
//≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡
function gfsl_validate_people_limit( $validation_result ) {
    $form = $validation_result['form'];

    if ( !rgar( $form, 'limitPeople' ) ) return $validation_result;

    $count_fields    = gfsl_get_count_fields($form);
    $count_field_ids = $count_fields ? wp_list_pluck($count_fields, 'id') : false;

    if ( !$count_field_ids ) return $validation_result;

    $requested = 0;
    foreach ($count_field_ids as $count_field_id) {
        $requested += intval(rgpost('input_'.$count_field_id) ?: 0);
    }

    $count     = gfsl_get_people_count($form);
    $total     = $requested + $count;
    $available = $form['limitPeopleCount'] - $count;

    if ( $total > $form['limitPeopleCount'] ) {
        $validation_result['is_valid'] = false;

        foreach ($form['fields'] as &$field) {
            if ( in_array($field->id, $count_field_ids) ) {
                $field->failed_validation = true;
                $field->validation_message = "There are only {$available} spots total remaining.";
            }
        }
    }

    return $validation_result;
}
add_filter( 'gform_validation', 'gfsl_validate_people_limit' );

function gfsl_maybe_hide_limited_form($form_string, $form) {
    if ( !rgar( $form, 'limitPeople' ) ) return $form_string;

    $count = gfsl_get_people_count($form);

    if ( $count >= $form['limitPeopleCount'] ) {
        return empty( $form['limitPeopleMessage'] ) ? "<div class='gf_submission_limit_message'><p>" . esc_html__( 'Sorry. This form is no longer accepting new submissions.', 'gravityforms' ) . '</p></div>' : '<p>' . GFCommon::gform_do_shortcode( $form['limitPeopleMessage'] ) . '</p>'; 
    }

    return $form_string;
}
add_filter( 'gform_get_form_filter', 'gfsl_maybe_hide_limited_form', 10, 2);

//≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡
// ✅ Shortcodes
//≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡≡
function gfsl_remaining_spots_shortcode($atts = []) {
    extract(shortcode_atts(array(
        'id' => false
    ), $atts));

    if ( !$id ) return;

    $form = GFAPI::get_form($id);

    if ( !$form ) return;

    $count     = gfsl_get_people_count($form);
    $available = $form['limitPeopleCount'] - $count;

    if ( !$available ) return;

    return '<p class="gfsl-spots-remaining">' . "{$available} spots remaining" . '<p>';
}
add_shortcode('remainingspots', 'gfsl_remaining_spots_shortcode');
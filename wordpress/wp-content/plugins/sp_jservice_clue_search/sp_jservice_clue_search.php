<?php

/*
Plugin name: JService Clue Searching Tool
Plugin URI: http://localhost:8000
Description: Search JService Clues
Author: Christopher Rogers
Author URI: http://localhost:8000
Version: 1.0.0
*/

if (!defined('ABSPATH')) {
    exit;
}

include(plugin_dir_path(__FILE__) . 'includes/JServiceClue.php');

register_uninstall_hook(__FILE__, 'sp_jservice_clue_search_uninstall');
add_action('admin_menu', 'sp_jservice_clue_search_menu');
register_activation_hook(__FILE__, 'sp_jservice_clue_search_activate');
add_action('admin_post_sp_jservice_clue_create_post', 'sp_jservice_clue_create_post');
add_action('wp_ajax_sp_jservice_clue_search_action', 'sp_jservice_clue_search_action');
wp_register_style('pure', 'https://unpkg.com/purecss@1.0.0/build/pure-min.css');
wp_enqueue_style('pure');
wp_enqueue_script('JServiceClue', plugin_dir_url(__FILE__) . 'js/JServiceClue.js', array('jquery'));

function sp_jservice_clue_search_activate()
{
    //call recursive function that will pull all clues with pagination
    //and save it to something?
    //$jServiceClueObj = new JServiceClue([]);
    //$jServiceClueObj->getClues();
}

function sp_jservice_clue_search_uninstall()
{
    //undo anything permanent done in activate?
}

function sp_jservice_clue_search_menu()
{
    add_menu_page('Clue Search Page', 'Clue Search', 'manage_options', 'sp_jservice_clue_search_menu', 'sp_jservice_clue_search_menu_option');
}

function sp_jservice_clue_search_menu_option()
{
    sp_jservice_clue_search_form();
}

function sp_jservice_clue_search_form()
{
    $html = '
        <span><p>Use the form below to search jservice.io for clues</p></span>';

    $html .= '
        <form class="pure-form pure-form-aligned" id="clueSearchForm" action="">
            <fieldset>
                <div class="pure-control-group">
                    <label for="value">Value</label>
                    <input id="value" type="text" name="value" placeholder="0">
                </div>
                
                <div class="pure-control-group">
                    <label for="category">Category</label>
                    <input id="category" type="text" name="category" placeholder="100">
                </div>
                
                <div class="pure-control-group">
                    <label for="minDate">Min Date</label>
                    <input id="minDate" type="text" name="minDate" placeholder="YYYY-MM-DD">
                </div>
                
                <div class="pure-control-group">
                    <label for="maxDate">Max Date</label>
                    <input id="maxDate" type="text" name="maxDate" placeholder="YYYY-MM-DD">
                </div>
                
                <div class="pure-control-group">
                    <label for="offset">Offset</label>
                    <input id="offset" type="text" name="offset" placeholder="0">
                </div>
        
                <div class="pure-controls">
                    <button type="submit" id="clueSearch" class="pure-button pure-button-primary">Submit</button>
                </div>
            </fieldset>
        </form>
        <div id="clueSearchResults"></div>
        ';

    echo $html;
}

function sp_jservice_clue_create_post()
{
    $postCreationSummary = 'There was an error, and your post was not saved';

    if (!empty($_POST)) {
        $post = array(
            'post_title' => $_POST['question'],
            'post_content' => $_POST['answer'],
            'post_type' => 'jeopardy',
        );

        if (wp_insert_post($post)) {
            $postCreationSummary = 'Post created!';
        }
    }

    echo $postCreationSummary;
    header("refresh:1;url=/wp-admin/admin.php?page=sp_jservice_clue_search_menu");
    wp_die();
}

function sp_jservice_clue_search_action()
{
    $jServiceClueObj = new JServiceClue($_POST);
    $errors = $jServiceClueObj->getErrors();

    if (empty($errors)) {
        $question = $answer = '';
        $html = '<table class="pure-table">';
        $cluesArray = json_decode($jServiceClueObj->getClues(), true);
        $html .= '<thead><tr>';
        $html .= '<th>Question</th>';
        $html .= '<th>Answer</th>';
        $html .= '<th>Value</th>';
        $html .= '<th>Save</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($cluesArray as $clues) {
            $html .= '<tr>';

            foreach ($clues as $key => $value) {
                if ('question' === $key) {
                    $question = htmlspecialchars($value);
                    $html .= '<td>' . $question . '</td>';
                } else if ('answer' === $key) {
                    $answer = htmlspecialchars($value);
                    $html .= '<td>' . $answer . '</td>';
                } else if ('value' === $key) {
                    $html .= '<td>' . htmlspecialchars($value) . '</td>';
                }
            }

            $html .= '<td>' .
                '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post">' .
                '<input type="hidden" id="question" name="question" value="' . $question . '">' .
                '<input type="hidden" id="answer" name="answer" value="' . $answer . '">' .
                '<input type="hidden" name="action" value="sp_jservice_clue_create_post">' .
                '<input type="submit" value="Save">' .
                '</form></td></tr>';
        }

        $html .= '</tbody></table>';
    } else {
        $html = 'something broke.. maybe a clue will follow' . PHP_EOL;

        foreach ($errors as $error) {
            $html .= $error . PHP_EOL;
        }
    }

    echo $html;
    wp_die();
}
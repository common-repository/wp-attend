<?php
/**
 * Manage subscriptions.
 *
 * @package WP_Attend
 */

$wpAtListTable = new WP_AT_List_Table(array(), new Functions(), new EmailHandler());
$wpAtListTable->prepare_items();
echo '<h3>'.__('Manage subscriptions', 'wp-attend').'</h3>'.
           '<div class="subscriptions-div">'.
                '<form method="post">'.
                    '<input type="hidden" name="page" value="wp_attend"/>';
					$wpAtListTable->search_box(__('search', 'wp-attend'), 'search_id');
                echo '</form>';
                $wpAtListTable->display();
           echo '</div>';


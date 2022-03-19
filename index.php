<?php
/**
 * Plugin Name:     WP-CLI Clean and Reassign Users from Multisite
 * Plugin URI:      https://metodiew.com
 * Description:     WP CLI plugin to cleans users from a Multisite and helps preparing better localhost details
 * Version:         0.1.0
 * Author:          Stanko Metodiev
 * Author URL:      https://metodiew.com
 */

/**
 * The command won't delete all users and will keep them in the network. You can delete them from
 * domain.com/wp-admin/network/users.php page.
 *
 * Below is an example command that would delete a specific user from the whole network, using wpmu_delete_user.
 * In future version of the CLI command we might extend this and do a complete deletion.
 */

/*
$deleted = wpmu_delete_user( $user_id );
if ( $deleted ) {
    WP_CLI::success( 'User ' . $user_id . ' has been deleted.' );
}
*/

if ( defined( 'WP_CLI' ) && WP_CLI ) {
class WP_Clean_And_Reassign_Users_From_MS extends WP_CLI_Command {

    /**
     * With this command we are cleaning users from all subsites in the Network and reassign their content to
     * the selected user here.
     *
     * The command checks if the user is a Super Admin. Based on the result the command throws an error or 
     * continues with the execution
     * Example: wp dx ms_clean_reassign_users USER_ID
     */
    public function ms_clean_reassign_users( $super_admin_id ) {

        // We need to make sure we have user input.
        if ( empty( $super_admin_id ) ) {
            WP_CLI::error( 'Enter User ID.' );
            exit;
        }

        // We need to make sure we are running on a Multisite, otherwise we can use the native WP-CLI command.
        if ( ! is_multisite() ) {
            WP_CLI::error( 'You are not running on a Multisite. You can use the native WP-CLI command here https://developer.wordpress.org/cli/commands/user/delete/.' );
            exit;
        }

        // WP-CLI converts this to an array with a string, so we have to use the int value.
        $super_admin_id = (int) $super_admin_id[0];

        // We need to make sure the user is a Super Admin, otherwise we can't proceed further.
        if ( ! is_super_admin( $super_admin_id ) ) {
            WP_CLI::error( 'The selected user is not a Super Admin! We can\'t proceed.' );
            exit;
        }

        if ( wp_is_large_network() ) {
            WP_CLI::error( 'This seems to be a Large network, you might want to be careful. I have exit() here, you might want to update me.' );
            exit();
        }

        // Get all subsites network
        $subsites = get_sites();

        // Delete all users from all subsites
        foreach ( $subsites as $subsite ) {
            $blog_id = (int) $subsite->blog_id;
            $blog_url = $subsite->domain . $subsite->path;

            WP_CLI::warning( 'Starting with ' . $blog_url . ' .' );

            self::delete_reasign_subsite_users( $blog_id, $super_admin_id );

            WP_CLI::success( 'Ready with ' . $blog_url . ' .' );
        }
    }


    private function delete_reasign_subsite_users( $site_id, $super_admin_id ) {
        if ( empty( $site_id ) || empty( $super_admin_id) ) {
            exit;
        }
        // We need to switch to the selected subsite.
        switch_to_blog( $site_id );

        // We would like to have the Super Admin as a subsite user, so you can see the site in My Sites dropdown
        add_user_to_blog( $site_id, $super_admin_id, 'administrator' );

        $users = get_users();

        foreach( $users as $user ) {
            $user_id = $user->ID;

            // We would like to keep the super admin added to the subsite
            if ( $user_id == $super_admin_id ) {
                continue;
            }

            wp_delete_user( $user_id, $super_admin_id );

            WP_CLI::success( 'User ID ' . $user_id .' has been deleted and the content has been reassigned to User ID ' . $super_admin_id );
        }

        // we have to restore to the current blog, always.
        restore_current_blog();
    }
}

WP_CLI::add_command( 'dx', 'WP_Clean_And_Reassign_Users_From_MS' );
}
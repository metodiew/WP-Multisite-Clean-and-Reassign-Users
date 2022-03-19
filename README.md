# WP-Multisite-Clean-and-Reassign-Users
This is a small WP-CLI command that deletes users from subsites in a Multisite Network.

The tool would be appropriate for running on localhost installations where you need a clean safe setup with no production users.

Example use case: you are working on a client project and you don't want to have real users on the site. You can create a new Super Admin user (or edit an existing one) and you can safely delete the user's content from the network, so you can delete users.

## Usage
We have to make a few assumptions
* You are familiar with WP-CLI
* You are running a multisite
* You need to clean existing users, in most cases for nice clean local installation

If these above are true, you can clone the repository in your `wp-content/plugins directory or use it as a `mu-plugin` and activate it.

You can use `wp dx ms_clean_reassign_users 99` where `99` is the ID of the Super Admin user you want to re-assign the content.

**An important note**
The command will NOT delete the users from the Multisite network. You have to go and delete them manually from `Network Admin > Users`.

If you like to delete any of the users, you can run something like
```
$deleted = wpmu_delete_user( $user_id );
if ( $deleted ) {
    WP_CLI::success( 'User ' . $user_id . ' has been deleted.' );
}
```
or extend the command.

In a future version, I might extend the command.

## Changelog
2022 March 19
* Initial release of the command.
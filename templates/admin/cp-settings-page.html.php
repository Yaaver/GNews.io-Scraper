<div class="wrap">
    <h2>GNews API Settings</h2>
    <?php settings_errors( 'gnews_settings' ); ?>  <form method="post" action="options.php">
        <?php settings_fields( 'gnews_settings' ); ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="gnews_api_key">API Key</label></th>
                <td>
                    <input type="text" name="gnews_api_key" id="gnews_api_key" value="<?php echo esc_attr( get_option( 'gnews_api_key' ) ); ?>">
                    <p class="description">Enter your GNews API key to access news articles.</p>
                </td>
            </tr>
            </tbody>
        </table>
        <?php submit_button(); ?>
    </form>
</div>

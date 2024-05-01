<div class="wrap">
    <h1>News Scraper</h1>

    <form id="fetch-news-form">
        <div class="keyword-row">
            <label for="q">Keyword:</label>
            <input type="text" id="q" name="q" placeholder="Enter Keyword (Default: News)">
        </div>

        <div class="category-row">
            <label for="category">Category:</label>
            <select id="category" name="category">
                <option value=""></option>
            </select>

            <label for="country">Country:</label>
            <select id="country" name="country">
                <option value="pk">Pakistan</option>
                <option value="gb">United Kingdom</option>
                <option value="us">United States</option>
            </select>
        </div>

        <div class="number-of-articles-row">
            <label for="lang">Language:</label>
            <input type="text" id="lang" name="lang" placeholder="Language (Default: en)">

            <label for="max_articles">Number of Articles:</label>
            <input type="number" id="max" name="max" min="1" value="5">
        </div>
        <div id="fetch-data">
            <button type="submit" id="fetch-news-button">Fetch News</button>
            <span><img src="<?php echo YK_CustomPlugin_URL ?>assets/img/loader.gif"</span>
        </div>
    </form>

    <script>
        jQuery(document).ready(function ($) {
            let ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>'; // Replace with your actual admin_url
            let nonce = '<?php echo wp_create_nonce('customplugin_nonce'); ?>';
            let formID = '#fetch-news-form';
            let fetchNewsButtonID = '#fetch-news-button';
            let loaderGif = '#fetch-data img';

            $(formID).submit(function (e) {
                e.preventDefault(); // Prevent default form submission

                var formData = $(this).serializeArray(); // Get form data as an array

                // Loop through form data and update missing values
                $.each(formData, function(index, item) {
                    if (item.name === 'q' && !item.value) {
                        item.value = 'news'; // Set default keyword to "news"
                    } else if (item.name === 'category' && !item.value) {
                        item.value = 'general'; // Set default category slug to "general"
                    } else if (item.name === 'lang' && !item.value) {
                        item.value = 'en'; // Set default language to "en"
                    }
                });

                var data = { // Prepare data object for AJAX request
                    action: 'yk_fetch_news',
                    nonce: nonce,
                    news_data: formData
                };

                // Clear any existing messages
                $(formID).nextAll('p').remove();
                $.ajax({
                    url: ajaxUrl,
                    type: 'post',
                    data: data,
                    beforeSend: function () {
                        // Show a loading indicator before fetching
                        $(fetchNewsButtonID).text('Fetching...');
                        $(loaderGif).show();
                    },
                    success: function (response) {
                        console.log(JSON.stringify(response));
                        if (response.success) {
                            $(formID).after('<p>' + response.data.message + '</p>');
                        } else {
                            $(formID).after('<p>' + response.data + '</p>');
                        }
                        // Handle successful response
                        $(fetchNewsButtonID).text('News Fetched');
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // Handle errors
                        $(fetchNewsButtonID).text('Error');
                        console.error('Error fetching news:', textStatus, errorThrown);
                        $(formID).after('<p>Error fetching news:</p><p>' + errorThrown + '</p>');
                    },
                    complete: function () {
                        $(loaderGif).hide();
                        // Always run this code after success or error
                        console.log('Fetching news completed.');
                    }
                });
            });
        });
    </script>

</div>
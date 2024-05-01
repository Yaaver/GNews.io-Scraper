jQuery(document).ready(function ($) {
    let ajaxUrl = customplugin_object.ajax_url;
    let nonce = customplugin_object.customplugin_nonce;

    // Fetch categories from ACF using AJAX
    function fetchAcfCategories() {
        $.ajax({
            url: ajaxUrl,
            type: 'post',
            data: {
                action: 'fetch_acf_categories', // Custom action for fetching categories
                nonce: nonce,
            },
            success: function (data) {
                if (data.success && data.data.success && data.data.categories) {
                    // Access categories from nested data structure
                    const categories = data.data.categories;

                    // Remove the default "All Categories" option (if present)
                    $('#category').empty(); // Clear existing options

                    // Populate the dropdown with retrieved categories
                    $.each(categories, function (index, category) {
                        const option = $('<option></option>').val(category.slug).text(category.name);
                        if (category.slug === 'general') {
                            option.attr('selected', true); // Set "General" as selected (optional)
                        }
                        $('#category').append(option);
                    });
                } else {
                    console.error('Error fetching ACF categories');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error fetching ACF categories:', textStatus, errorThrown);
            }
        });
    }

    // Call the function to fetch categories on document ready
    fetchAcfCategories();
});

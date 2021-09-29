/**
 * @file node view statistics
 */
(function ($, Drupal, drupalSettings) {
  $(document).ready(function () {
    const statistics_url =
      (drupalSettings.ffw_test_module.url.length > 0) ? drupalSettings.ffw_test_module.url : '';

    if (!statistics_url) {
      return;
    }

    $.ajax({
      type: 'POST',
      cache: false,
      url: Drupal.url(statistics_url),
    });
  });
})(jQuery, Drupal, drupalSettings, window.localStorage);
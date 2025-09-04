<?php
/**
 * GeneratePress child theme functions and definitions.
 *
 * Add your custom PHP in this file.
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */

// Allow SVG
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {

  global $wp_version;
  if ($wp_version !== '4.7.1') {
    return $data;
  }

  $filetype = wp_check_filetype($filename, $mimes);

  return [
    'ext' => $filetype['ext'],
    'type' => $filetype['type'],
    'proper_filename' => $data['proper_filename']
  ];

}, 10, 4);

function cc_mime_types($mimes)
{
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

function fix_svg()
{
  echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
             width: 100% !important;
             height: auto !important;
        }
        </style>';
}
add_action('admin_head', 'fix_svg');

function current_year()
{
  $year = date('Y');
  return $year;
}

add_shortcode('year', 'current_year');

// Core-Block-CSS nur „on demand“ laden
add_filter('should_load_separate_core_block_assets', '__return_true');

// Lazy-Loading für Bilder aktivieren
add_filter('wp_lazy_loading_enabled', '__return_true');

add_filter('generate_logo_output', function ($output) {
  if (strpos($output, 'is-logo-image') !== false) {
    $output = str_replace('<img', '<img loading="lazy" decoding="async"', $output);
  }
  return $output;
});

// Schnellste Lösung - alle Bilder lazy außer Featured Image
add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment) {
  // Nur Featured Image der aktuellen Seite eager laden
  if (get_post_thumbnail_id() === $attachment->ID) {
    $attr['loading'] = 'eager';
    $attr['fetchpriority'] = 'high';
  } else {
    $attr['loading'] = 'lazy';
  }
  return $attr;
}, 10, 2);

// Zusätzlich: Content-Bilder
add_filter('the_content', function ($content) {
  return preg_replace('/<img(?![^>]*loading=)/', '<img loading="lazy"', $content);
});

add_filter('render_block', function ($block_content, $block) {
  if (strpos($block_content, 'Tennisakademie_Logo_weiss.svg') !== false) {
    $block_content = str_replace('<img', '<img loading="lazy" decoding="async"', $block_content);
  }
  return $block_content;
}, 10, 2);

// Titel für Frontend-Seiten (Pages) ausgeben verhindern
add_filter('the_title', 'rs_hide_page_titles_in_frontend', 10, 2);
function rs_hide_page_titles_in_frontend($title, $post_id)
{
  // nichts im Admin-Bereich ändern
  if (is_admin())
    return $title;

  // nur Haupt-Loop & nur Pages
  if (is_page() && in_the_loop() && !is_admin()) {
    // optional: bestimmte Seite ausschließen
    // if ($post_id == 123) return $title;
    return '';
  }
  return $title;
}

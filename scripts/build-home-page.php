<?php
/**
 * Build editable homepage content once; reruns preserve existing page sections.
 * Run: ddev drush en image_effects deepcuts_home -y
 *      ddev drush php:script scripts/build-home-page.php
 */
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\image\Entity\ImageStyle;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\user\Entity\Role;
use Drupal\views\Entity\View;

function dc_home_field($entity, $bundle, $name, $type, $label, $settings = [], $widget = 'string_textfield', $storage = []): void {
  if (!FieldStorageConfig::loadByName($entity, $name)) {
    FieldStorageConfig::create(['entity_type' => $entity, 'field_name' => $name, 'type' => $type] + $storage)->save();
  }
  if (!FieldConfig::loadByName($entity, $bundle, $name)) {
    FieldConfig::create(['entity_type' => $entity, 'bundle' => $bundle, 'field_name' => $name, 'label' => $label, 'settings' => $settings])->save();
  }
  $form = EntityFormDisplay::load("$entity.$bundle.default") ?: EntityFormDisplay::create(['targetEntityType' => $entity, 'bundle' => $bundle, 'mode' => 'default', 'status' => TRUE]);
  $form->setComponent($name, ['type' => $widget])->save();
}
if (!NodeType::load('logo')) { NodeType::create(['type' => 'logo', 'name' => 'Logo', 'description' => 'Company logos displayed in the homepage strip.', 'new_revision' => TRUE, 'display_submitted' => FALSE])->save(); }
dc_home_field('node', 'logo', 'field_logo', 'image', 'Company logo (PNG)', ['file_extensions' => 'png', 'file_directory' => 'company-logos', 'alt_field' => TRUE, 'alt_field_required' => TRUE, 'title_field' => FALSE], 'image_image', ['settings' => ['target_type' => 'file']]);
$logo_form = EntityFormDisplay::load('node.logo.default');
$logo_form->setComponent('title', ['type'=>'string_textfield', 'weight'=>-10])->setComponent('status', ['type'=>'boolean_checkbox', 'weight'=>10])->save();
if (!ImageStyle::load('deepcuts_home_intro')) {
  $intro_style = ImageStyle::create(['name'=>'deepcuts_home_intro','label'=>'Homepage introduction (1280px)']);
  $intro_style->addImageEffect(['id'=>'image_scale','weight'=>0,'data'=>['width'=>1280,'height'=>NULL,'upscale'=>FALSE]]);
  $intro_style->save();
}
if (!ImageStyle::load('logo')) {
  $style = ImageStyle::create(['name' => 'logo', 'label' => 'Logo']);
  $style->addImageEffect(['id' => 'image_effects_color_shift', 'weight' => 0, 'data' => ['RGB' => '#444444']]);
  $style->addImageEffect(['id' => 'image_desaturate', 'weight' => 1, 'data' => []]);
  $style->addImageEffect(['id' => 'image_scale', 'weight' => 2, 'data' => ['width' => 80, 'height' => 80, 'upscale' => FALSE]]);
  $style->save();
}
$display = EntityViewDisplay::load('node.logo.default') ?: EntityViewDisplay::create(['targetEntityType' => 'node', 'bundle' => 'logo', 'mode' => 'default', 'status' => TRUE]);
$display->setComponent('field_logo', ['type' => 'image', 'label' => 'hidden', 'settings' => ['image_style' => 'logo', 'image_link' => '', 'image_loading' => ['attribute' => 'lazy']]])->removeComponent('links')->save();
$role = Role::load('editor');
foreach (['create logo content', 'edit any logo content', 'delete any logo content'] as $permission) { $role->grantPermission($permission); } $role->save();
$dir = 'public://company-logos';
\Drupal::service('file_system')->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
$names = ['gq'=>'GQ', 'royal-air-force'=>'Royal Air Force', 'royal-navy'=>'Royal Navy', 'british-army'=>'British Army', 'sky'=>'Sky', 'ritz'=>'The Ritz', 'savoy'=>'The Savoy', 'abbey-road-studios'=>'Abbey Road Studios', 'facebook'=>'Facebook', 'soho-house'=>'Soho House', 'babington-house'=>'Babington House', 'grosvenor-house'=>'Grosvenor House', 'deloitte'=>'Deloitte', 'toyota'=>'Toyota'];
$logo_sources = glob(DRUPAL_ROOT . '/_resources/Company Logos/*') ?: glob(DRUPAL_ROOT . '/_resources/Company logos/*');
if (!$logo_sources) { throw new \RuntimeException('Company logo source files are missing.'); }
foreach ($logo_sources as $source) {
  $slug = pathinfo($source, PATHINFO_FILENAME); $title = $names[$slug] ?? ucwords(str_replace('-', ' ', $slug));
  if (\Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'logo', 'title' => $title])) { continue; }
  $bytes = file_get_contents($source);
  if (strtolower(pathinfo($source, PATHINFO_EXTENSION)) !== 'png') { $im = imagecreatefromstring($bytes); ob_start(); imagepng($im); $bytes = ob_get_clean(); imagedestroy($im); }
  $uri = "$dir/$slug.png"; $existing = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $uri]);
  $file = $existing ? reset($existing) : \Drupal::service('file.repository')->writeData($bytes, $uri, FileExists::Error);
  $file->setPermanent(); $file->save(); $size = getimagesizefromstring($bytes);
  Node::create(['type'=>'logo', 'title'=>$title, 'uid'=>1, 'status'=>1, 'promote'=>0, 'field_logo'=>['target_id'=>$file->id(), 'alt'=>$title, 'width'=>$size[0], 'height'=>$size[1]]])->save();
}
// Nested cards keep their copy, image, destination and order editable on Home.
foreach (['feature_card'=>'Feature card', 'feature_cards'=>'Feature card group', 'home_reviews'=>'Homepage reviews'] as $id=>$label) {
  if (!ParagraphsType::load($id)) { ParagraphsType::create(['id'=>$id, 'label'=>$label])->save(); }
}
foreach ([['field_heading','string','Heading','string_textfield'], ['field_text','text_long','Text','text_textarea'], ['field_section_image','entity_reference','Image','media_library_widget'], ['field_link','link','Destination','link_default']] as [$field,$type,$label,$widget]) {
  $settings = $field === 'field_section_image' ? ['handler'=>'default:media', 'handler_settings'=>['target_bundles'=>['image'=>'image']]] : [];
  dc_home_field('paragraph','feature_card',$field,$type,$label,$settings,$widget);
  $form = EntityFormDisplay::load('paragraph.feature_card.default');
  $component = $form->getComponent($field); $component['weight'] = array_search($field, ['field_heading','field_section_image','field_text','field_link']);
  $form->setComponent($field, $component)->save();
}
dc_home_field('paragraph','feature_cards','field_cards','entity_reference_revisions','Cards',['handler'=>'default:paragraph', 'handler_settings'=>['target_bundles'=>['feature_card'=>'feature_card']]],'paragraphs',['cardinality'=>-1,'settings'=>['target_type'=>'paragraph']]);
dc_home_field('paragraph','home_reviews','field_items','entity_reference','First review',['handler'=>'default:node', 'handler_settings'=>['target_bundles'=>['review'=>'review']]],'entity_reference_autocomplete');
$display = EntityViewDisplay::load('paragraph.feature_card.default') ?: EntityViewDisplay::create(['targetEntityType'=>'paragraph','bundle'=>'feature_card','mode'=>'default','status'=>TRUE]);
$display->setComponent('field_text',['type'=>'text_default','label'=>'hidden'])->removeComponent('field_heading')->removeComponent('field_link')->removeComponent('field_section_image')->save();
// Allow new top-level sections in the Page form, but not standalone cards.
$field = FieldConfig::loadByName('node','page','field_sections'); $settings = $field->getSetting('handler_settings');
foreach (['feature_cards','home_reviews'] as $type) { $settings['target_bundles'][$type]=$type; $settings['target_bundles_drag_drop'][$type]=['weight'=>20,'enabled'=>TRUE]; }
$field->setSetting('handler_settings',$settings)->save();
// Published Logo entities, ordered by title, output as image-only rows.
$base = View::load('deepcuts_review')->toArray(); unset($base['uuid']); $base['id']='deepcuts_logos'; $base['label']='Deep Cuts: homepage logos'; $base['dependencies']=[];
$base['display'] = ['default'=>$base['display']['default']];
$options = &$base['display']['default']['display_options'];
$options['filters']['type']['value']=['logo'=>'logo']; unset($options['filters']['field_featured_value']);
$options['sorts']=['title'=>['id'=>'title','table'=>'node_field_data','field'=>'title','entity_type'=>'node','entity_field'=>'title','plugin_id'=>'standard','order'=>'ASC']];
$options['row']['options']['view_mode']='default';
if (!View::load('deepcuts_logos')) { View::create($base)->save(); }
$base['id']='deepcuts_home_reviews'; $base['label']='Deep Cuts: homepage reviews';
$options['filters']['type']['value']=['review'=>'review']; $options['sorts']=[];
$options['pager']=['type'=>'some','options'=>['items_per_page'=>5,'offset'=>0]];
$options['cache']=['type'=>'none']; $options['row']['options']['view_mode']='dc_card';
if (!View::load('deepcuts_home_reviews')) { View::create($base)->save(); }
$directory='public://homepage'; \Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
$import = static function ($filename, $alt) use ($directory): Media {
  $name='Home: '.pathinfo($filename,PATHINFO_FILENAME); $existing=\Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['bundle'=>'image','name'=>$name]); if ($existing) { return reset($existing); }
  $source=DRUPAL_ROOT.'/_resources/Images/Homepage/'.$filename; $uri=$directory.'/'.strtolower(str_replace(' ','-',$filename));
  $files=\Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri'=>$uri]); $file=$files?reset($files):\Drupal::service('file.repository')->writeData(file_get_contents($source),$uri,FileExists::Error); $file->setPermanent();$file->save();$size=getimagesize($source);
  $media=Media::create(['bundle'=>'image','name'=>$name,'uid'=>1,'status'=>1,'field_media_image'=>['target_id'=>$file->id(),'alt'=>$alt,'width'=>$size[0],'height'=>$size[1]]]); $media->save();return $media;
};
$home=Node::load(1);
if ($home->field_sections->isEmpty()) {
  $intro=$import('Homepage intro header.jpg','Deep Cuts performing live together');
  $paragraphs=[];
  $save=static function(array $values): Paragraph { $p=Paragraph::create($values);$p->save();return $p; };
  $paragraphs[]=$save(['type'=>'image_text','field_heading'=>'For those who truly love their music LIVE','field_text'=>['value'=>'<p>Tired of the same overplayed tunes on mimed promo videos? Say hello to Deep Cuts; a world-class band for those who truly love their music LIVE.</p>','format'=>'basic_html'],'field_section_image'=>$intro->id(),'field_image_position'=>'left','field_bordered'=>1,'field_link'=>['uri'=>'internal:/about','title'=>'Find out more']]);
  $groups=[
    [
      ['Weddings','Your biggest day deserves the very best. From an unforgettable night of live music to acoustic accompaniments for your ceremony, we can tailor the setlist and arrangement to your style.','/book-now'],
      ['Corporate events','Looking to celebrate a successful end-of-year or perhaps you want to host clients at an all-out shindig? Show them a night to remember with live entertainment that they’ll talk about for weeks.','/book-now'],
      ['Private parties','Whether it’s a milestone celebration or a once-in-a-lifetime event, we work with you to create an exclusive musical experience for you and your guests.','/book-now'],
    ],
    [
      ['Packages','From weddings and corporate events to private parties and international bookings, we offer tailored packages to suit every occasion. Choose the perfect lineup, performance, and production for your event.','/packages'],
      ['Testimonials','Cumulatively, we have over 40 years of experience and happy clients – but don’t just take our word for it! Read what previous clients have loved about our performances.','/testimonials'],
      ['Videos','See us in action with content that was recorded fully LIVE. Both our electric and acoustic sets showcase a sample of our setlist and the energy that we will bring to your day.','/videos'],
    ],
  ];
  foreach ($groups as $index=>$cards) {
    $refs=[];
    foreach ($cards as [$title,$text,$destination]) { $image=$import($title.'.jpg', $title.' with Deep Cuts'); $card=$save(['type'=>'feature_card','field_heading'=>$title,'field_text'=>['value'=>'<p>'.htmlspecialchars($text,ENT_QUOTES,'UTF-8').'</p>','format'=>'basic_html'],'field_section_image'=>$image->id(),'field_link'=>['uri'=>'internal:'.$destination,'title'=>$title]]); $refs[]=['target_id'=>$card->id(),'target_revision_id'=>$card->getRevisionId()]; }
    $paragraphs[]=$save(['type'=>'feature_cards','field_cards'=>$refs]);
    if ($index===0) { $paragraphs[]=$save(['type'=>'home_reviews','field_items'=>187]); }
  }
  foreach (\Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type'=>'review','status'=>1]) as $review) { $review->setPromoted(TRUE);$review->save(); }
  $home->set('field_sections',array_map(static fn($p)=>['target_id'=>$p->id(),'target_revision_id'=>$p->getRevisionId()],$paragraphs));
  $home->set('field_hero_video',10); $home->set('field_hero_image',$intro->id()); $home->setNewRevision(TRUE);$home->setRevisionLogMessage('Build Figma homepage with editable cards, Media 10, logos and promoted reviews.');$home->save();
}
// Export only related configuration, preserving unrelated work in the sync dir.
$exact_exports = ['core.extension', 'user.role.editor', 'node.type.logo',
  'image.style.logo', 'image.style.deepcuts_home_intro',
  'views.view.deepcuts_logos', 'views.view.deepcuts_home_reviews',
  'field.storage.node.field_logo', 'field.storage.paragraph.field_cards',
  'field.field.node.page.field_sections'];
$prefixes = ['field.field.node.logo.', 'core.entity_form_display.node.logo.',
  'core.entity_view_display.node.logo.'];
foreach (['feature_card', 'feature_cards', 'home_reviews'] as $bundle) {
  $exact_exports[] = 'paragraphs.paragraphs_type.' . $bundle;
  foreach (['field.field.paragraph.', 'core.entity_form_display.paragraph.', 'core.entity_view_display.paragraph.'] as $prefix) {
    $prefixes[] = $prefix . $bundle . '.';
  }
}
foreach (\Drupal::service('config.storage')->listAll() as $name) {
  $export = in_array($name, $exact_exports, TRUE);
  foreach ($prefixes as $prefix) { $export = $export || str_starts_with($name, $prefix); }
  if ($export) {
    file_put_contents(DRUPAL_ROOT . '/sync/' . $name . '.yml', \Drupal\Core\Serialization\Yaml::encode(\Drupal::config($name)->getRawData()));
  }
}
echo "Homepage ready: editable sections, 14 logos and five reviews per request.\n";

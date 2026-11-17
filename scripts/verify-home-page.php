<?php
/** Read-only homepage, permissions and View selection checks. */
use Drupal\node\Entity\Node;
use Drupal\views\Views;
$failures=[];
$check=static function($ok,$message) use (&$failures) { echo ($ok?'PASS ':'FAIL ').$message.PHP_EOL; if(!$ok)$failures[]=$message; };
$home=Node::load(1);
$check($home->field_hero_video->target_id==10,'Home reuses Media 10');
$sections=$home->field_sections->referencedEntities();
$check(array_map(fn($p)=>$p->bundle(),$sections)===['image_text','feature_cards','home_reviews','feature_cards'],'All Figma sections in order');
foreach([$sections[1],$sections[3]] as $group) {
  $check($group->field_cards->count()===3,'Three editable cards per group');
  foreach($group->field_cards->referencedEntities() as $card) { $check(!$card->field_text->isEmpty() && !$card->field_section_image->isEmpty() && !$card->field_link->isEmpty(),'Complete card: '.$card->field_heading->value); }
}
$logos=Views::getView('deepcuts_logos');$logos->execute();$check(count($logos->result)===14,'Logo View contains all 14 source logos');
foreach($logos->result as $row) { $logo=Node::load($row->nid);$file=$logo->field_logo->entity; $check($file->getMimeType()==='image/png','PNG logo: '.$logo->label()); }
$style=\Drupal\image\Entity\ImageStyle::load('logo');
$effects=array_values($style->getEffects()->getConfiguration());
$check(array_column($effects,'id')===['image_effects_color_shift','image_desaturate','image_scale'],'Logo effects in requested order');
$check($effects[0]['data']['RGB']==='#444444' && $effects[2]['data']['width']==80 && $effects[2]['data']['height']==80,'Logo shift and scale settings');
$pinned=(int)$sections[2]->field_items->target_id;$selections=[];
for($i=0;$i<12;$i++) {
  $view=Views::getView('deepcuts_home_reviews');$view->setArguments([$pinned]);$view->execute();$ids=array_map(fn($r)=>(int)$r->nid,$view->result);$selections[]=implode(',',$ids);
  $check(count($ids)===5 && count(array_unique($ids))===5 && $ids[0]===$pinned,'Five distinct reviews, Figma quote first');
  foreach(array_slice($ids,1) as $id) { if(!Node::load($id)->isPromoted())$failures[]='Unpromoted review selected'; }
}
$check(count(array_unique($selections))>1,'Other four reviews vary across requests');
$editor=\Drupal\user\Entity\Role::load('editor');
$check($editor->hasPermission('create logo content') && $editor->hasPermission('edit any logo content') && $editor->hasPermission('delete any logo content'),'Editor can maintain logos');
$check(!$editor->hasPermission('administer site configuration') && !$editor->hasPermission('administer views'),'Editor cannot change fundamental configuration');
if($failures) throw new RuntimeException(implode('; ',$failures));
echo "All homepage checks passed.\n";

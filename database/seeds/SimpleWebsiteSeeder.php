<?php

use Illuminate\Database\Seeder;
use App\Models\Home;
use App\Models\User;
use App\Models\Entity;
use Cuatromedios\Kusikusi\Models\Relation;
use Cuatromedios\Kusikusi\Models\EntityContent;

class SimpleWebsiteSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {

    $homeEntity = new Entity([
        "model" => Home::modelId(),
        "id" => Home::modelId(),
        "name" => ucfirst(Home::modelId()),
        "parent_id" => 'root',
    ]);
    $homeEntity->contents()->save(new EntityContent(["field" => "title", "value"=>"El home", "lang" => "en"]));
    $homeEntity->contents()->save(new EntityContent(["field" => "summary", "value"=>"Bienvenidos"]));
    $homeEntity->contents()->save(new EntityContent(["footer" => "Teléfono"]));
    $homeEntity->addContents(["keywords" => "a,b,c", "address" => "none"]);
    $homeEntity->save();


    $adminEntity = new Entity([
        "model" => "userx",
        "parent_id" => $homeEntity->id
    ]);
    $adminEntity->addContents(["cv" => "The user CV", "bio" => "My bio"]);
    $adminEntity->addRelation(['id' => $homeEntity->id, 'kind' => 'home', 'tags' => '1,2,3', 'depth'=>3, 'position'=>4]);
    $homeEntity->addRelation(['id' => $adminEntity->id, 'kind' => 'like', 'tags' => '4,5,6', 'depth'=>0, 'position'=>0]);

    $adminUser = new User([
        "name" => "Admin",
        "email" => "admin",
        "password" => "admin",
        "profile" => User::PROFILE_ADMIN,
      ]);
    $adminEntity->user()->save($adminUser);
    $adminEntity->save();

    $homes = Entity::ofModel('home')
        ->select('id', 'model')
        ->with(['relations'=>function($query) {
          $query->select('id', 'model')
              ->with(['contents' => function($query) {$query->where('field', '=', 'title');}])
              ->with('user')
              ->where('kind', '=', 'like');
          }])
        ->with(['contents' => function($query) {$query->where('field', '=', 'summary')->orWhere('field', '=', 'cv');}])
        ->get();

    $grouped = array_map("Cuatromedios\Kusikusi\Models\EntityContent::reduce", $homes->toArray());
    var_dump($grouped[0]['relations'][0]);
  }
}

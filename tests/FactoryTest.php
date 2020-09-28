<?php

namespace Soyhuce\LaravelFactoriesExtended\Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Orchestra\Testbench\TestCase;
use Soyhuce\LaravelFactoriesExtended\Factory;

class FactoryTest extends TestCase
{
    protected function setUp(): void
    {
        Container::getInstance()->singleton(\Faker\Generator::class, function ($app, $parameters) {
            return \Faker\Factory::create('en_US');
        });

        $db = new DB();

        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();

        $this->createSchema();
    }

    /**
     * Setup the database schema.
     *
     * @return void
     */
    public function createSchema()
    {
        $this->schema()->create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('options')->nullable();
            $table->timestamps();
        });

        $this->schema()->create('posts', function ($table) {
            $table->increments('id');
            $table->foreignId('user_id');
            $table->string('title');
            $table->timestamps();
        });

        $this->schema()->create('comments', function ($table) {
            $table->increments('id');
            $table->foreignId('commentable_id');
            $table->string('commentable_type');
            $table->string('body');
            $table->timestamps();
        });

        $this->schema()->create('roles', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('role_user', function ($table) {
            $table->foreignId('role_id');
            $table->foreignId('user_id');
            $table->string('admin')->default('N');
        });
    }

    /**
     * @test
     */
    public function ofBelongsToRelationship()
    {
        $user = FactoryTestUserFactory::new(['name' => 'Taylor Otwell'])->create();
        $posts = FactoryTestPostFactory::times(3)
            ->of($user, 'user')
            ->create();

        $this->assertCount(3, $user->posts);

        $this->assertCount(1, FactoryTestUser::all());
        $this->assertCount(3, FactoryTestPost::all());
    }

    /**
     * @test
     */
    public function ofMorphToRelationship()
    {
        $post = FactoryTestPostFactory::new(['title' => 'Test Title'])->create();
        $comments = FactoryTestCommentFactory::times(3)
            ->of($post, 'commentable')
            ->create();

        $this->assertCount(3, $post->comments);

        $this->assertCount(1, FactoryTestPost::all());
        $this->assertCount(3, FactoryTestComment::all());
    }

    /**
     * @test
     */
    public function dynamicOfMethod()
    {
        $user = FactoryTestUserFactory::new(['name' => 'Taylor Otwell'])->create();
        $posts = FactoryTestPostFactory::times(3)
            ->ofAuthor($user)
            ->create();

        $this->assertCount(3, $user->posts);

        $post = FactoryTestPostFactory::new(['title' => 'Test Title'])->create();
        $comments = FactoryTestCommentFactory::times(3)
            ->ofCommentable($post)
            ->create();

        $this->assertCount(3, $post->comments);
    }

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    protected function connection()
    {
        return Eloquent::getConnectionResolver()->connection();
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function schema()
    {
        return $this->connection()->getSchemaBuilder();
    }
}

class FactoryTestUserFactory extends Factory
{
    protected $model = FactoryTestUser::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'options' => null,
        ];
    }
}

class FactoryTestUser extends Eloquent
{
    use HasFactory;

    protected $table = 'users';

    public function posts()
    {
        return $this->hasMany(FactoryTestPost::class, 'user_id');
    }
}

class FactoryTestPostFactory extends Factory
{
    protected $model = FactoryTestPost::class;

    public function definition()
    {
        return [
            'user_id' => FactoryTestUserFactory::new(),
            'title' => $this->faker->name,
        ];
    }
}

class FactoryTestPost extends Eloquent
{
    protected $table = 'posts';

    public function user()
    {
        return $this->belongsTo(FactoryTestUser::class, 'user_id');
    }

    public function author()
    {
        return $this->belongsTo(FactoryTestUser::class, 'user_id');
    }

    public function comments()
    {
        return $this->morphMany(FactoryTestComment::class, 'commentable');
    }
}

class FactoryTestCommentFactory extends Factory
{
    protected $model = FactoryTestComment::class;

    public function definition()
    {
        return [
            'commentable_id' => FactoryTestPostFactory::new(),
            'commentable_type' => FactoryTestPost::class,
            'body' => $this->faker->name,
        ];
    }
}

class FactoryTestComment extends Eloquent
{
    protected $table = 'comments';

    public function commentable()
    {
        return $this->morphTo();
    }
}

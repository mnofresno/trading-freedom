<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $users = [ ['name' => 'Ryan Chenkie'   , 'email' => 'ryanchenkie@gmail.com', 'password' => 'secret' ],
                   ['name' => 'Chris Sevilleja', 'email' => 'chris@scotch.io'      , 'password' => 'secret' ],
                   ['name' => 'Holly Lloyd'    , 'email' => 'holly@scotch.io'      , 'password' => 'secret' ],
                   ['name' => 'Adnan Kukic'    , 'email' => 'adnan@scotch.io'      , 'password' => 'secret' ] ];

        foreach ($users as $user)
        {
            User::create($user);
        }

        Model::reguard();
    }
}

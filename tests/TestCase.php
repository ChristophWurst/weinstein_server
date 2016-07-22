<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase {
	
	/**
     * Try to log user in
     * 
     * @param string $username
     * @return User
     */
    protected function login($username) {
        $user = User::find($username);
        $this->assertFalse(is_null($user));
        
        $this->assertFalse(Auth::check());
        $this->be($user);
        $this->assertTrue(Auth::check());
        $this->assertEquals($user, Auth::user());
        
        return $user;
    }

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}

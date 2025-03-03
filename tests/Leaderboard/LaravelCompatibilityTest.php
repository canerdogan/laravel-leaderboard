<?php

namespace Tests\Leaderboard;

use CanErdogan\Leaderboard\LeaderboardServiceProvider;
use CanErdogan\Leaderboard\LeaderboardHandler;
use CanErdogan\Leaderboard\Facades\Leaderboard;
use Illuminate\Foundation\Application;
use Mockery;
use Tests\AbstractTestCase;

/**
 * Test compatibility with Laravel 10, 11, and 12
 */
class LaravelCompatibilityTest extends AbstractTestCase
{
    /**
     * Test that the service provider can be instantiated with Laravel 10+ application
     */
    public function testServiceProviderInstantiation()
    {
        $app = new Application();
        $provider = new LeaderboardServiceProvider($app);
        
        $this->assertInstanceOf(LeaderboardServiceProvider::class, $provider);
    }
    
    /**
     * Test that the service provider registers the leaderboard handler with Laravel 10+ application
     */
    public function testServiceProviderRegistersHandler()
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('singleton')
            ->with(LeaderboardHandler::class, Mockery::type('Closure'))
            ->once();
            
        $app->shouldReceive('singleton')
            ->with('CanErdogan\Leaderboard\Console\Kernel', Mockery::type('Closure'))
            ->once();
            
        $app->shouldReceive('make')
            ->with('CanErdogan\Leaderboard\Console\Kernel')
            ->once();
            
        $provider = new LeaderboardServiceProvider($app);
        $provider->register();
        
        $this->assertTrue(true); // If we got here without exceptions, the test passes
    }
    
    /**
     * Test that the facade accessor returns the correct class name
     */
    public function testFacadeAccessor()
    {
        $reflection = new \ReflectionClass(Leaderboard::class);
        $method = $reflection->getMethod('getFacadeAccessor');
        $method->setAccessible(true);
        
        $this->assertEquals(LeaderboardHandler::class, $method->invoke(null));
    }
    
    /**
     * Test that the service provider provides method returns the correct services
     */
    public function testServiceProviderProvides()
    {
        $app = new Application();
        $provider = new LeaderboardServiceProvider($app);
        
        $provides = $provider->provides();
        
        $this->assertIsArray($provides);
        $this->assertContains(LeaderboardHandler::class, $provides);
    }
    
    /**
     * Test that return type declarations are properly implemented
     */
    public function testReturnTypeDeclarations()
    {
        $reflection = new \ReflectionClass(LeaderboardServiceProvider::class);
        
        $bootMethod = $reflection->getMethod('boot');
        $this->assertEquals('void', $bootMethod->getReturnType()->getName());
        
        $registerMethod = $reflection->getMethod('register');
        $this->assertEquals('void', $registerMethod->getReturnType()->getName());
        
        $providesMethod = $reflection->getMethod('provides');
        $this->assertEquals('array', $providesMethod->getReturnType()->getName());
    }
}
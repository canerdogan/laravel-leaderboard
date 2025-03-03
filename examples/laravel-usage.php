<?php
/**
 * Laravel Leaderboard Package Usage Example
 * 
 * This file demonstrates how to use the Laravel Leaderboard package
 * within a Laravel application.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CanErdogan\Leaderboard\Facades\Leaderboard;

class GameController extends Controller
{
    /**
     * Display the game leaderboard
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showLeaderboard(Request $request)
    {
        // Get the all-time leaderboard for a specific game
        $allTimeLeaders = Leaderboard::getLeaderboard([
            'featureId' => 'game1',
            'fromRank' => 0,
            'toRank' => 9 // Top 10 players
        ]);
        
        // Get the daily leaderboard
        $dailyLeaders = Leaderboard::getLeaderboard([
            'leaderboard' => 'daily',
            'featureId' => 'game1',
            'fromRank' => 0,
            'toRank' => 9
        ]);
        
        // Get the weekly leaderboard
        $weeklyLeaders = Leaderboard::getLeaderboard([
            'leaderboard' => 'weekly',
            'featureId' => 'game1',
            'fromRank' => 0,
            'toRank' => 9
        ]);
        
        // Get the monthly leaderboard
        $monthlyLeaders = Leaderboard::getLeaderboard([
            'leaderboard' => 'monthly',
            'featureId' => 'game1',
            'fromRank' => 0,
            'toRank' => 9
        ]);
        
        // Format the leaderboard data for the view
        $allTimeData = $this->formatLeaderboardData($allTimeLeaders, 'alltime');
        $dailyData = $this->formatLeaderboardData($dailyLeaders, 'daily');
        $weeklyData = $this->formatLeaderboardData($weeklyLeaders, 'weekly');
        $monthlyData = $this->formatLeaderboardData($monthlyLeaders, 'monthly');
        
        // Return the view with the leaderboard data
        return view('game.leaderboard', [
            'allTimeLeaderboard' => $allTimeData,
            'dailyLeaderboard' => $dailyData,
            'weeklyLeaderboard' => $weeklyData,
            'monthlyLeaderboard' => $monthlyData
        ]);
    }
    
    /**
     * Submit a new score for a user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitScore(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'user_id' => 'required|string',
            'score' => 'required|numeric',
            'game_id' => 'required|string'
        ]);
        
        // Insert the score
        Leaderboard::insertScore(
            $validated['user_id'],
            $validated['score'],
            [
                'featureId' => $validated['game_id'],
                'scoreData' => [
                    'level' => $request->input('level', 1),
                    'time' => $request->input('time', 0),
                    'bonus' => $request->input('bonus', 0)
                ]
            ]
        );
        
        // Redirect back with success message
        return redirect()->back()->with('success', 'Score submitted successfully!');
    }
    
    /**
     * Get the rank and score for a specific user
     *
     * @param Request $request
     * @param string $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserStats(Request $request, $userId)
    {
        $gameId = $request->input('game_id', 'game1');
        $leaderboardType = $request->input('leaderboard', 'alltime');
        
        // Get the user's rank
        $rank = Leaderboard::getRank($userId, [
            'leaderboard' => $leaderboardType,
            'featureId' => $gameId
        ]);
        
        // Get the user's best score
        $score = Leaderboard::getUserBestScore($userId, [
            'leaderboard' => $leaderboardType,
            'featureId' => $gameId
        ], [
            'rawscore' => true,
            'scoreData' => true,
            'date' => true
        ]);
        
        // Get players around the user
        $aroundMe = Leaderboard::getAroundMeLeaderboard($userId, [
            'leaderboard' => $leaderboardType,
            'featureId' => $gameId,
            'range' => 5
        ]);
        
        // Format the "around me" data
        $aroundMeData = $this->formatLeaderboardData($aroundMe, $leaderboardType);
        
        // Return the data as JSON
        return response()->json([
            'user_id' => $userId,
            'rank' => $rank !== null ? $rank + 1 : null, // Add 1 to make it 1-based
            'score' => $score,
            'around_me' => $aroundMeData
        ]);
    }
    
    /**
     * Format the leaderboard data for display
     *
     * @param array $leaders
     * @param string $leaderboardType
     * @return array
     */
    private function formatLeaderboardData($leaders, $leaderboardType)
    {
        $formattedData = [];
        
        foreach ($leaders as $index => $userId) {
            $rank = $index + 1;
            $score = Leaderboard::getUserBestScore($userId, [
                'leaderboard' => $leaderboardType,
                'featureId' => 'game1'
            ], [
                'rawscore' => true,
                'scoreData' => true,
                'date' => true
            ]);
            
            $formattedData[] = [
                'rank' => $rank,
                'user_id' => $userId,
                'score' => $score['rawScore'] ?? 0,
                'date' => $score['date'] ?? null,
                'score_data' => json_decode($score['scoreData'] ?? '{}', true)
            ];
        }
        
        return $formattedData;
    }
}
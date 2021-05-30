<?php

namespace App\Console\Commands;

use GameQ\GameQ;
use App\Models\Portal\User;
use App\Models\Missions\Map;
use Illuminate\Console\Command;
use App\Models\Missions\Mission;
use App\Models\Portal\Attendance;
use App\Models\Operations\Operation;

class CollectAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:collect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collects attendance data about all members.';

    /**
     * Mission ID.
     *
     * @var integer
     */
    protected $missionId;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($missionId = null)
    {
        parent::__construct();

        $this->missionId = $missionId;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle($runningInConsole = true)
    {
        $operation = Operation::where('starts_at', 'like', now()->toDateString() . '%')->first();

        if (!$operation) {
            echo 'Operation not found';
            return;
        }

        $minutesOver = $operation->starts_at->diffInMinutes(now()->addHour(), false);

        if ($minutesOver <= 0) {
            echo 'Operation hasn\'t started yet';
            return;
        }

        $users = User::all();

        $present = collect();
        $absent = collect();

        $query = new GameQ;

        $query->addServer([
            'id' => 'main',
            'type' => 'armedassault3',
            'host' => '108.61.34.58:2302',
            'options' => [
                'debug' => true,
                'timeout' => 10
            ]
        ]);

        $results = json_decode(json_encode($query->process()['main']));

        while (!isset($results->map)) {
            $results = json_decode(json_encode($query->process()['main']));
            sleep(1);
        }

        $missions = [];
        $currentMission = null;

        if (!$this->missionId) {
            if ($map = Map::select('id')->whereClassName(optional($results)->map)->first()) {
                $missions = Mission::whereMapId($map->id)->get();
            }

            foreach ($missions as $mission) {
                $parts = explode('/', $mission->cloud_pbo);
                $name = str_replace_last(".{$results->map}.pbo", '', end($parts));

                if (strtolower($name) === strtolower($results->gq_gametype)) {
                    $currentMission = $mission;
                    break;
                }
            }

            if (!$currentMission) {
                echo 'Mission not found';
                return;
            }
        } else {
            $currentMission = Mission::findOrFail($this->missionId);
        }

        foreach ($users as $user) {
            $found = false;

            foreach ($results->players as $player) {
                $name = $player->name;

                $name = preg_replace('(\[.*\])', '', $name);
                $name = preg_replace('(\d)', '', $name);
                $name = str_slug(strtolower(trim($name)), '-');

                $hubName = str_slug(strtolower(trim($user->username)));

                if (str_contains($hubName, $name)) {
                    $found = true;
                    $present->push("{$user->username} marked as present");

                    Attendance::updateOrCreate([
                        'user_id' => $user->id,
                        'operation_id' => $operation->id,
                        'mission_id' => optional($currentMission)->id,
                    ], ['present' => true]);

                    break;
                }
            }

            if (!$found) {
                $absent->push("{$user->username} marked as absent");

                Attendance::updateOrCreate([
                    'user_id' => $user->id,
                    'operation_id' => $operation->id,
                    'mission_id' => optional($currentMission)->id,
                ], ['present' => false]);
            }
        }
    }
}

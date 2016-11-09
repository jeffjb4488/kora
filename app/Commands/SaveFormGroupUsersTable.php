<?php namespace App\Commands;

use Carbon\Carbon;
use App\RichTextField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveFormGroupUsersTable extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Form Group Users table.");

        $table_path = $this->backup_filepath . "/form_group_user/";
        $table_array = $this->makeBackupTableArray("form_group_user");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DB::table('form_group_user')->chunk(500, function($fgUsers) use ($table_path, $row_id) {
            $count = 0;
            $all_formgroupuser_data = new Collection();

            foreach($fgUsers as $formgroupuser) {
                $individual_formgroupuser_data = new Collection();

                $individual_formgroupuser_data->put("form_group_id", $formgroupuser->form_group_id);
                $individual_formgroupuser_data->put("user_id", $formgroupuser->user_id);

                $all_formgroupuser_data->push($individual_formgroupuser_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_formgroupuser_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}
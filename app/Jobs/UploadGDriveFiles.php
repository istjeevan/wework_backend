<?php

namespace App\Jobs;

use App\Events\Propertyevent;
use App\Models\PropertiesImages;
use Google\Service\Drive;
use Google_Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadGDriveFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $property;
    protected $createdProperty;
    protected $userid;
    protected $imageStatus;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($property, $createdProperty,$userid,$imageStatus)
    {
        $this->property = $property;
        $this->createdProperty = $createdProperty;
        $this->userid = $userid;
        $this->imageStatus = $imageStatus;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Image uploading for property ' . $this->createdProperty->id);

        try {

            $pathoffile = storage_path('/app/gdrive/cred.json');

            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $pathoffile);

            $client = new Google_Client();

            $client->useApplicationDefaultCredentials();
            $client->addScope(Drive::DRIVE);

            $service = new Drive($client);

            sleep(5);

            $msg = array(
                'message' => __('Property :p image uploading...', ['p' => $this->createdProperty->id]),
                'success' => true,
                'status' => 'uploading',
            );
    
            event(new Propertyevent($msg, $this->userid));

            if (str_contains($this->property['gallery_folder'], 'https://drive.google.com/drive')) {

                $gDriveUrl = $this->property['gallery_folder'];
                $gDriveUrlArray = explode('/', $gDriveUrl);
                $galleryId = end($gDriveUrlArray);

            } else {
                $galleryId = $this->property['gallery_folder'];
            }

            $gallerySearch = array(
                'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,id,name,webViewLink,mimeType,parents)",
                'mimeType' => 'application/vnd.google-apps.folder',
                "q" => "'{$galleryId}' in parents",
                'supportsAllDrives' => true,
            );

            $results = $service->files->listFiles($gallerySearch);

            if (count($results->getFiles()) !== 0) {

                foreach ($results->getFiles() as $file) {

                    if (str_contains($file->mimeType, 'image')) {

                        // $content = $this->service->files->get($file->getId());
                        $filedata = $service->files->get($file->getId(), ["alt" => "media"]);
                        $filename = time() . '.' . $file->fileExtension;
                        @file_put_contents(public_path() . "/new_images/{$filename}", $filedata->getBody());

                        /** Insert property image */

                        $this->createdProperty->images()->create([
                            'image_name' => $filename,
                            'created_at' => now()
                        ]);

                        sleep(1);

                    }

                }
            }

            if ($this->property['tos_file_folder']) {

                if (str_contains($this->property['tos_file_folder'], 'https://drive.google.com/drive')) {

                    $gDriveFileUrl = $this->property['tos_file_folder'];
                    $gDriveFileArray = explode('/', $gDriveFileUrl);
                    $filegDriveId = end($gDriveFileArray);

                } else {
                    $filegDriveId = $this->property['tos_file_folder'];
                }

                $fileSearch = array(
                    'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,iconLink,id,name,size,thumbnailLink,webContentLink,webViewLink,mimeType,parents)",
                    'mimeType' => 'application/vnd.google-apps.folder',
                    "q" => "'{$filegDriveId}' in parents",
                    'supportsAllDrives' => true,
                );

                $results2 = $service->files->listFiles($fileSearch);

                if (count($results2->getFiles()) !== 0) {

                    foreach ($results2->getFiles() as $pdffile) {

                        if (str_contains($pdffile->mimeType, 'application/pdf')) {

                            $filedata2 = $service->files->get($pdffile->getId(), ["alt" => "media"]);
                            $pdffilename = time() . '.' . $pdffile->fileExtension;
                            @file_put_contents(public_path() . "/terms_and_condition/{$pdffilename}", $filedata2->getBody());

                            $this->createdProperty->terms_and_condition_file = $pdffilename;
                            $this->createdProperty->save();

                            sleep(1);

                        }

                    }
                }

            }

            if ($this->property['thumbnail_folder']) {

                if (str_contains($this->property['thumbnail_folder'], 'https://drive.google.com/drive')) {

                    $gDriveThumbUrl = $this->property['thumbnail_folder'];
                    $gDriveThumbArray = explode('/', $gDriveThumbUrl);
                    $fileThumbId = end($gDriveThumbArray);

                } else {
                    $fileThumbId = $this->property['thumbnail_folder'];
                }

                $thumbnailSearch = array(
                    'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,iconLink,id,name,size,thumbnailLink,webContentLink,webViewLink,mimeType,parents)",
                    'mimeType' => 'application/vnd.google-apps.folder',
                    "q" => "'{$fileThumbId}' in parents",
                    'supportsAllDrives' => true,
                );

                $results3 = $service->files->listFiles($thumbnailSearch);

                if (count($results3->getFiles()) !== 0) {

                    foreach ($results3->getFiles() as $thumbfile) {

                        if (str_contains($thumbfile->mimeType, 'image/')) {

                            $filedata3 = $service->files->get($thumbfile->getId(), ["alt" => "media"]);
                            $thumbFileName = time() . '.' . $thumbfile->fileExtension;
                            @file_put_contents(public_path() . "/new_images/{$thumbFileName}", $filedata3->getBody());

                            $this->createdProperty->thumbnail_image = $thumbFileName;
                            $this->createdProperty->save();

                            sleep(1);

                        }

                    }
                }

            }

            Log::info('Uploading images of property ' . $this->createdProperty->id . ' finished !');

            if($this->imageStatus == 'uploaded'){
                $msg = array(
                    'message' => __('Property :p image upload finished !', ['p' => $this->createdProperty->id]),
                    'success' => true,
                    'status' => 'uploaded',
                );
        
                event(new Propertyevent($msg, $this->userid));
                Log::info('All properties images upload finished !');
            }

            

        } catch (\Exception $e) {

            Log::error(__('Property :p image upload fail reason :r', [
                'p' => $this->createdProperty->id,
                'r' => $e->getMessage(),
            ]));

            $this->failed($e, $this->createdProperty->id);

        }

    }

    public function failed(\Exception $e, $pid)
    {
        $args = array(
            'message' => __('Error uploading images at property :p please check logs for info',[
                'p' => $pid
            ]),
            'success' => false,
            'status' => 'uploading',
        );

        event(new Propertyevent($args, $this->userid));

        $this->delete();
    }
}

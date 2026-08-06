<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use App\Models\DriversDocuments;
use App\Helpers\Helper;

class DocumentsController extends Controller
{

    public function __construct()
    {
        $this->limit = 20;
    }

    public function getData(Request $request)
    {
        try {
            $admin_documents = DB::table('admin_documents')->where('is_enabled', 'Yes')->get();
            $output = [];
            foreach ($admin_documents as $row) {
                $row->id = (string)$row->id;
                $output[] = $row;
            }
            if (count($output) > 0) {
                $response['success'] = 'success';
                $response['error'] = null;
                $response['message'] = 'successfully';
                $response['data'] = $output;
            } else {
                $response['success'] = 'Failed';
                $response['error'] = 'No documents found';
                $response['message'] = 'No documents found';
                $response['data'] = [];
            }
            return response()->json($response);
        } catch (\Throwable $e) {
            \Log::error('DocumentsController getData error: ' . $e->getMessage());
            return response()->json([
                'success' => 'Failed',
                'error' => $e->getMessage(),
                'message' => 'An error occurred while fetching document types.',
                'data' => []
            ]);
        }
    }

    public function addDriverDocuments(Request $request)
    {
        try {
            $driver_id = $request->get('driver_id');
            $documents = $request->get('documents');
            $attachment = $request->file('attachment');

            if (empty($driver_id) || $driver_id == 0) {
                return response()->json(['success' => 'Failed', 'error' => 'Driver Not Found']);
            }
            if (empty($documents)) {
                return response()->json(['success' => 'Failed', 'error' => 'Documents Not Found']);
            }
            if (empty($attachment)) {
                return response()->json(['success' => 'Failed', 'error' => 'Attachment Not Found']);
            }

            $documents = json_decode($documents);
            if (!is_array($documents) && !is_object($documents)) {
                return response()->json(['success' => 'Failed', 'error' => 'Invalid Documents payload']);
            }

            $targetDir = public_path('assets/images/driver/documents');
            if (!file_exists($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            foreach ($documents as $data) {
                $document_id = $data->document_id ?? null;
                $attachmentIndex = $data->attachmentIndex ?? null;

                if ($attachmentIndex !== null && isset($attachment[$attachmentIndex])) {
                    $image_path = $attachment[$attachmentIndex];
                    $extenstion = strtolower($image_path->getClientOriginalExtension());
                    $document_name = DB::table('admin_documents')->where('id', $document_id)->first();
                    $title = ($document_name && !empty($document_name->title)) ? $document_name->title : 'Document';

                    $filename = str_replace(' ', '_', $title) . '_' . time() . '_' . rand(100, 999) . '.' . $extenstion;
                    $uploadedUrl = null;
                    if (!empty(config('imagekit.private_key'))) {
                        try {
                            $uploadedUrl = Helper::uploadToImageKit($image_path, '/driver/documents');
                        } catch (\Throwable $e) {
                            \Log::warning('ImageKit upload for driver document failed, falling back to local: ' . $e->getMessage());
                        }
                    }

                    if ($uploadedUrl) {
                        $filename = $uploadedUrl;
                    } else {
                        $image_path->move($targetDir, $filename);
                    }

                    $existingDoc = DB::table('driver_document')
                        ->where('document_id', $document_id)
                        ->where('driver_id', $driver_id)
                        ->first();

                    if ($existingDoc) {
                        DB::table('driver_document')->where('id', $existingDoc->id)->update([
                            'document_path' => $filename,
                            'document_status' => 'Pending',
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('driver_document')->insert([
                            'driver_id' => $driver_id,
                            'document_id' => $document_id,
                            'document_path' => $filename,
                            'document_status' => 'Pending',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => 'success',
                'error' => null,
                'message' => 'Documents Added Successfully'
            ]);
        } catch (\Throwable $e) {
            \Log::error('addDriverDocuments error: ' . $e->getMessage());
            return response()->json([
                'success' => 'Failed',
                'error' => $e->getMessage(),
                'message' => 'An error occurred while uploading document.'
            ]);
        }
    }

    public function updateDriverDocuments(Request $request)
    {
        try {
            $driver_id = $request->get('driver_id');
            $document_id = $request->get('document_id');
            $file = $request->file('attachment');

            if ($document_id === null || $document_id === '') {
                return response()->json(['success' => 'Failed', 'error' => 'Document Id Not Found']);
            }
            if (empty($driver_id) || $driver_id == 0) {
                return response()->json(['success' => 'Failed', 'error' => 'Driver Id Not Found']);
            }
            if (empty($file)) {
                return response()->json(['success' => 'Failed', 'error' => 'Attachment Not Found']);
            }

            $document_name = DB::table('admin_documents')->where('id', $document_id)->first();
            $title = ($document_name && !empty($document_name->title)) ? $document_name->title : 'Document';

            $targetDir = public_path('assets/images/driver/documents');
            if (!file_exists($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }

            $extenstion = strtolower($file->getClientOriginalExtension());
            $isImage = in_array($extenstion, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

            $filename = str_replace(' ', '_', $title) . '_' . time() . '_' . rand(100, 999) . '.' . $extenstion;
            $uploadedUrl = null;
            if (!empty(config('imagekit.private_key'))) {
                try {
                    $uploadedUrl = Helper::uploadToImageKit($file, '/driver/documents');
                } catch (\Throwable $e) {
                    \Log::warning('ImageKit upload for driver document failed, falling back to local: ' . $e->getMessage());
                }
            }

            if ($uploadedUrl) {
                $filename = $uploadedUrl;
            } else {
                $file->move($targetDir, $filename);
            }

            $get_driver_document = DB::table('driver_document')
                ->where('document_id', $document_id)
                ->where('driver_id', $driver_id)
                ->first();

            if ($get_driver_document) {
                if (!filter_var($get_driver_document->document_path, FILTER_VALIDATE_URL) && file_exists($targetDir . '/' . $get_driver_document->document_path)) {
                    @unlink($targetDir . '/' . $get_driver_document->document_path);
                }
                DB::table('driver_document')->where('id', $get_driver_document->id)->update([
                    'document_path' => $filename,
                    'document_status' => 'Pending',
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('driver_document')->insert([
                    'driver_id' => $driver_id,
                    'document_id' => $document_id,
                    'document_path' => $filename,
                    'document_status' => 'Pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $updatedDoc = DB::table('driver_document')
                ->where('document_id', $document_id)
                ->where('driver_id', $driver_id)
                ->first();

            if ($updatedDoc) {
                if (filter_var($updatedDoc->document_path, FILTER_VALIDATE_URL)) {
                    $updatedDoc->document_path = $updatedDoc->document_path;
                } else {
                    $updatedDoc->document_path = url('assets/images/driver/documents/' . $updatedDoc->document_path);
                }
                $updatedDoc->document_name = $title;
                $updatedDoc->id = $updatedDoc->document_id;

                return response()->json([
                    'success' => 'Success',
                    'error' => null,
                    'message' => $title . ' Updated',
                    'data' => $updatedDoc
                ]);
            }

            return response()->json([
                'success' => 'Failed',
                'error' => $title . ' Not Updated'
            ]);
        } catch (\Throwable $e) {
            \Log::error('updateDriverDocuments error: ' . $e->getMessage());
            return response()->json([
                'success' => 'Failed',
                'error' => $e->getMessage(),
                'message' => 'An error occurred while updating document.'
            ]);
        }
    }

    public function getDriverDocuments(Request $request)
    {
        try {
            $driver_id = $request->get('driver_id');
            $admin_documents = DB::table('admin_documents')->where('is_enabled', '=', 'Yes')->get();

            if (!empty($admin_documents)) {
                foreach ($admin_documents as $key => $document) {
                    $id = $document->id;
                    $get_driver_document = DB::table('driver_document')
                        ->where('document_id', $document->id)
                        ->where('driver_id', $driver_id)
                        ->first();
                    $document->id = (string)$id;
                    if ($get_driver_document) {
                        if (filter_var($get_driver_document->document_path, FILTER_VALIDATE_URL)) {
                            $document->document_path = $get_driver_document->document_path;
                        } else {
                            $document->document_path = url('assets/images/driver/documents/' . $get_driver_document->document_path);
                        }
                        $document->document_status = $get_driver_document->document_status;
                        $document->comment = $get_driver_document->comment;
                    } else {
                        $document->document_path = '';
                        $document->document_status = 'Pending';
                        $document->comment = '';
                    }
                    $document->document_name = $document->title;
                    $admin_documents[$key] = $document;
                }

                return response()->json([
                    'success' => 'success',
                    'error' => null,
                    'message' => 'successfully',
                    'data' => $admin_documents
                ]);
            }

            return response()->json([
                'success' => 'Failed',
                'error' => 'Failed to fetch data',
                'message' => 'No document types found'
            ]);
        } catch (\Throwable $e) {
            \Log::error('getDriverDocuments error: ' . $e->getMessage());
            return response()->json([
                'success' => 'Failed',
                'error' => $e->getMessage(),
                'message' => 'An error occurred while fetching driver documents.'
            ]);
        }
    }
}

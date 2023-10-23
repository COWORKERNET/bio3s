<?php
// Real

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Validator;

use App\Models\Banner;
use App\Models\Board;
use App\Models\Certificate;
use App\Models\Faq;
use App\Models\File;
use App\Models\Gallery;
use App\Models\History;
use App\Models\HistoryCategory;
use App\Models\Patent;
use App\Models\Popup;
use App\Models\Product;
use App\Models\ProductCertificate;
use App\Models\Service;
use App\Models\Social;
use App\Models\User;
use App\Models\ProductProcurement;
use App\Models\ProductProcurementList;
use App\Models\ProductProcurementGuideList;
use App\Models\ProductProcurementTableColumn;
use App\Models\ProductProcurementTableColumnHeader;

use Exception;


class AdminController extends Controller
{

    // 배너 리스트 출력
    public function banner () {

        $data = [];
        $msg = "";
        $success = false;

        try {

            $data = Banner::leftJoin('files', function($leftJoin) {
                $leftJoin->on('banners.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'banners')
                        ->where('files.status', '=', 0);
            })
            ->select('banners.id', 'banners.type', 'banners.title', 'banners.content', 'files.id as file_id' ,'files.fileAddr', 'files.ext', 'banners.created_at')
            ->where('banners.status', '=', 0)
            ->where('banners.ko', '=', 0)
            ->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // 연혁
    public function history () {

        $data = [];
        $msg = "";
        $success = false;

        // product : 상품 리스트

        try {

            $data = History::leftJoin('historyCategories', function($leftJoin) {
                $leftJoin->on('histories.ref_history_id', '=', 'historyCategories.id')
                            ->where('historyCategories.status', '!=', 1)
                            ->where('historyCategories.ko', '=', 0);
            })
            ->select(
                'histories.id', 
                'historyCategories.id as ref_id',
                'historyCategories.content as type',
                'historyCategories.year_start',
                'historyCategories.year_end',
                'histories.year', 
                'histories.month', 
                'histories.content', 
                'histories.created_at'
            )
            ->where('histories.status', '!=', 1)
            ->where('histories.ko', '=', 0)
            ->orderByDesc('histories.year')
            ->orderByDesc('histories.month')
            ->orderByDesc('histories.id')
            ->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    //
    public function historyCategory() {

        $data = [];
        $msg = "";
        $success = false;

        // product : 상품 리스트

        try {

            $data = historyCategory::select('*')
            ->where('ko', '=', 0)
            ->where('status', '!=', 1)
            ->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // 상품 리스트 출력
    public function product () {

        $data = [];
        $msg = "";
        $success = false;

        // product : 상품 리스트

        try {

            $data = Product::leftJoin('files', function($leftJoin) {
                $leftJoin->on('products.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'products')
                        ->where('files.status', '=', 0);
            })
            ->select('products.id', 'products.status', 'products.category' ,'products.title', 'products.content', 'products.tags', 'products.sel_link', 'products.short_content', 'files.fileAddr', 'products.created_at')
            ->where('products.status', '!=', 2)
            ->where('products.ko', '=', 0)
            ->orderByDesc('products.id')
            ->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // 상품 상세 [ 상품정보, 소셜정보, 선택된 인증서 정보<선택안된 인증서 정보 포함> ]
    public function detailProduct (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        try {

            $id = $request->input('id');

            // Product 귀속 SNS 데이터 조회
            $data['sns'] = Social::leftJoin('files', function($leftJoin) {
                $leftJoin->on('socials.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'socials')
                        ->where('files.status', '=', 0);
            })
            ->select('socials.id', 'socials.title', 'socials.link', 'files.fileAddr', 'files.origin_filename', 'files.ext')
            ->where('socials.status', '=', 0)
            ->where('socials.type', '=', 2)
            ->where('socials.ko', '=', 0)
            ->where('socials.ref_products_id', '=', $id)
            ->get();

            // 질의/응답 데이터 조회
            $data['faqs'] = Faq::select('id', 'title', 'content')
                                ->where('status', '=', 0)
                                ->where('ref_products_id', '=', $id)
                                ->where('ko', '=', 0)
                                ->orderByDesc('created_at')
                                ->get();

            // 인증서 데이터 조회
            $productInCertificate = ProductCertificate::where('ref_products_id', '=', $id)
                                                        ->where('status', '=', 0)
                                                        ->where('ko', '=', 0)
                                                        ->select('ref_certificates_id')
                                                        ->get();

            // 특정 상품에 관계된 인증서 고유번호 배열
            $certificateIds = $productInCertificate->toArray();

            // 상기 인증서 고유번호 배열을 이용한 인증서 정보 조회
            $data['certificate'] = Certificate::leftJoin('files', function($leftJoin) {
                $leftJoin->on('certificates.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'certificates')
                        ->where('files.status', '=', 0);

            })
            ->select('certificates.id', 'certificates.content', 'files.fileAddr', 'files.origin_filename')
            ->where('certificates.status', '=', 0)
            ->where('certificates.ko', '=', 0)
            ->whereIn('certificates.id', $certificateIds)
            ->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // 갤러리
    public function gallery () {

        $data = [];
        $msg = "";
        $success = false;

        // product : 상품 리스트

        try {

            $data = Gallery::leftJoin('files', function($leftJoin) {
                $leftJoin->on('galleries.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'galleries')
                        ->where('files.status', '=', 0);
            })

            ->select('galleries.id', 'galleries.type', 'galleries.content', 'files.fileAddr', 'files.origin_filename', 'files.ext', 'galleries.register_date', 'galleries.created_at')
            ->where('galleries.status', '=', 0)
            ->where('galleries.ko', '=', 0)            
            ->orderByDesc('galleries.id')
            ->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // 뉴스
    public function news () {

        $data = [];
        $msg = "";
        $success = false;

        // product : 상품 리스트

        try {
            
            $data = Board::select('id', 'status', 'count', 'title', 'content', 'created_at')
                    ->where('type', '=', 0)
                    ->where('status', '!=', 2)
                    ->where('ko', '=', 0)    
                    ->orderByDesc('status')
                    ->orderByDesc('created_at')
                    ->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // 공지
    public function notice () {

        $data = [];
        $msg = "";
        $success = false;

        // product : 상품 리스트

        try {

            $data = Board::select('id', 'status', 'count', 'title', 'content', 'created_at')
                    ->where('type', '=', 1)
                    ->where('status', '!=', 2)
                    ->where('ko', '=', 0)    
                    ->orderByDesc('status')
                    ->orderByDesc('created_at')
                    ->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function boardDetail (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        // 내용 및 첨부파일
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id = $request->input('id');

        try {

            $data['files'] = File::select('id', 'ref_type_id', 'filename', 'origin_filename', 'ext', 'fileAddr')
                            ->where('ref_type_id', '=', $id)
                            ->where('type', '=', 'boards')
                            ->where('status', '!=', 1)
                            ->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // 특허
    public function patent () {

        $data = [];
        $msg = "";
        $success = false;

        // product : 상품 리스트

        try {

            // 0 : 국내출원, 1 : 국내등록, 2 : 해외출원, 3 : 해외등록
            $data = Patent::select('id', 'title', 'type', 'number', 'writer', 'country', 'register_date', 'created_at')
                    ->where('status', '!=', 1)
                    ->where('ko', '=', 0)    
                    ->get();
            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // 소셜
    public function sns () {

        $data = [];
        $msg = "";
        $success = false;

        // product : 상품 리스트

        try {

            $data = Social::leftJoin('files', function($leftJoin) {
                $leftJoin->on('socials.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'socials')
                        ->where('files.status', '=', 0);
            })

            ->select('socials.id', 'socials.type', 'socials.link', 'socials.title', 'files.fileAddr', 'files.origin_filename', 'files.ext', 'socials.created_at')
            ->where('socials.status', '!=', 1)
            ->where('socials.type', '!=', 2)
            ->where('socials.ko', '=', 0)    
            ->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // 문의
    public function qna () {

        $data = [];
        $msg = "";
        $success = false;

        // product : 상품 리스트

        try {

            $data = Service::select('id', 'status', 'phone', 'name', 'email', 'content', 'company', 'category', 'created_at')
                    ->where('status', '!=', 2)
                    ->get();
            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // 팝업
    public function popup () {

        $data = [];
        $msg = "";
        $success = false;

        try {

            $table = Popup::query();

            $table->leftJoin('files', function($leftJoin) {
                $leftJoin->on('popups.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'popups')
                        ->where('files.status', '=', 0);
            });
            $table->select('popups.*', 'files.id as file_id' ,'files.fileAddr', 'files.ext');
            $table->where('popups.status', '!=', 1);
            $table->where('popups.ko', '=', 0);

            $data = $table->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // 팝업 상세
    public function detailPopup(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id = $request->input('id');

        try {

            $data = Popup::leftJoin('files', function($leftJoin) {
                $leftJoin->on('popups.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'popups')
                        ->where('files.status', '=', 0);
            })

            ->select('popups.*', 'files.fileAddr', 'files.origin_filename', 'files.ext')
            ->where('popups.status', '!=', 1)
            ->where('popups.ko', '=', 0)
            ->where('popups.id', '=', $id)
            ->first();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // 인증
    public function certificate () {

        $data = [];
        $msg = "";
        $success = false;

        try {

            $notVisibleList = ProductCertificate::select('ref_certificates_id')
                                                ->where('status', '!=', 1)
                                                ->get()->toArray();
            

            $data = Certificate::select('id', 'type', 'register_date', 'doc_status', 'number', 'content', 'created_at')
                    ->where('status', '!=', 1)
                    ->where('ko', '=', 0)
                    ->whereNotIn('id', $notVisibleList)
                    ->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function procurement() {

        $data = [];
        $msg = "";
        $success = false;

        try {

            // ProductProcurementList
            $productProcurementList = ProductProcurementList::query();

            $productProcurementList->select('id')->where('status', '!=', 1)->get();

            $query = ProductProcurement::query();

            $query->leftJoin('files', function($leftJoin) {
                $leftJoin->on('productProcurement.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'productProcurement')
                        ->where('files.status', '=', 0);
            });

            $query->select(
                'productProcurement.id',
                'productProcurement.status',
                'productProcurement.ref_procurementList_id',
                'productProcurement.type',
                'productProcurement.title', 
                'productProcurement.tags', 
                'productProcurement.created_at',
                'files.fileAddr'
            );

            $query->where('productProcurement.status', '!=', 1);
            $query->where('productProcurement.ko', '=', 0);
            $query->whereIn('productProcurement.ref_procurementList_id', $productProcurementList);

            // 관리자에서 상품 정보가 없는 상품 등록 시, 사용자 단에 노출되는 것을 제외하기 위한 예외 처리
            // $query->where('productProcurement.title', '!=', ''); 

            $query->groupBy('productProcurement.ref_procurementList_id');

            $query->orderByDesc('productProcurement.id');

            $data = $query->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function procurmentCategoryDetail(Request $request) {
        
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id'            => 'required',
            'type'          => 'required'
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id = $request->input('id');
        $type = $request->input('type');

        try {

            $productProcurement = ProductProcurement::query();
            $productProcurementGuideList = ProductProcurementGuideList::query();
            $productProcurementTableColumn = ProductProcurementTableColumn::query();
            $productProcurementTableColumnHeader = ProductProcurementTableColumnHeader::query();

            // 노출되는 카테고리만 조회
            $data['category'] = $productProcurement->select('type')
                                ->where('productProcurement.ref_procurementList_id', '=', $id)
                                ->where('productProcurement.ko', '=', 0)
                                ->get();


            // 노출이 확정된 데이터만 조회
            $productProcurement->leftJoin('files', function($leftJoin) {
                $leftJoin->on('productProcurement.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'productProcurement')
                        ->where('files.status', '=', 0);
            });

            $productProcurement->select(
                'productProcurement.id',
                'productProcurement.status',
                'productProcurement.type',
                'productProcurement.ref_procurementList_id',
                'productProcurement.title',
                'productProcurement.title',
                'productProcurement.short_content',
                'productProcurement.tags', 
                'productProcurement.deliverGuide', 
                'files.fileAddr'
            );
            
            $productProcurement->where('productProcurement.type', '=', $type);
            $productProcurement->where('productProcurement.ko', '=', 0);

            $data['detailInfo'] = $productProcurement->first();

            // 노출이 확정된 카테고리 중 기타 데이터 조회
            // type 으로 구분하여 각 각의 데이터를 가져오기 떄문에
            // detailInfo 데이터가 없는 경우 하위 데이터는 조회하지 않습니다.
            if(!empty($data['detailInfo'])) {

                $data['tableHeader'] = $productProcurementTableColumnHeader->select('id', 'ref_procurement_id', 'content')
                                        ->where('status', '!=', 1)
                                        ->where('ko', '=', 0)
                                        ->where('ref_procurement_id', '=', $data['detailInfo']['id'])
                                        ->get();

                $data['table'] = $productProcurementTableColumn->select('id', 'row', 'type', 'ref_table_header_id', 'content')
                                    ->where('status', '!=', 1)
                                    ->where('ko', '=', 0)
                                    ->where('ref_procurement_id', '=', $data['detailInfo']['id'])
                                    ->where('ref_procurement_type_id', '=', $type)
                                    ->orderBy('row', 'asc')
                                    ->orderBy('ref_table_header_id', 'asc')
                                    ->get();

                $productProcurementGuideList->leftJoin('files', function($leftJoin) {
                    $leftJoin->on('productProcurementGuideList.id', '=', 'files.ref_type_id')
                            ->where('files.type', '=', 'productProcurementGuideList')
                            ->where('files.status', '=', 0);
                });
                
                $productProcurementGuideList->select(
                    'productProcurementGuideList.id',
                    'productProcurementGuideList.type',
                    'productProcurementGuideList.content',
                    'files.fileAddr',
                )
                ->where('productProcurementGuideList.status', '!=', '1')
                ->where('productProcurementGuideList.ko', '=', 0)
                ->where('productProcurementGuideList.ref_procurement_id', '=', $id)
                ->where('productProcurementGuideList.ref_procurement_type_id', '=', $type)
                
                ->orderBy('productProcurementGuideList.type', 'asc')
                ->orderBy('productProcurementGuideList.id', 'asc');

                $data['guideList'] = $productProcurementGuideList->get();
                       
            }

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    /* ------------ REGISTER ------------ */

    public function registerProductId (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        try {

            $data['id'] = Product::insertGetId([
                'category' => "화장품",
                'title' => '',
                'ko' => 0,
                'content' => '',
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registerFaq (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $id = $request->input('id');

        try {

            $data['id'] = Faq::insertGetId([
                'ref_products_id' => $id,
                'ko' => 0,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registerBanner (Request $request) {

        //
        $data = [];
        $msg = "";
        $success = false;

        try {

            $data['id'] = Banner::insertGetId([
                'type' => 0,
                'title' => '',
                'content' => '',
                'ko' => 0,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = 'Error. : '.$e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registerPopup (Request $request) {

        //
        $data = [];
        $msg = "";
        $success = false;

        try {

            $data['id'] = Popup::insertGetId([
                'link' => '',
                'title' => '',
                'ko' => 0,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registerPatent (Request $request) {

        //
        $data = [];
        $msg = "";
        $success = false;

        try {

            $data['id'] = Patent::insertGetId([
                'title' => '',
                'type' => 0,
                'number' => '',
                'ko' => 0,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registerCertificate (Request $request) {

        //
        $data = [];
        $msg = "";
        $success = false;

        try {

            $data['id'] = Certificate::insertGetId([
                'ko' => 0,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registerGallery (Request $request) {

        //
        $data = [];
        $msg = "";
        $success = false;

        try {

            $data['id'] = Gallery::insertGetId([
                'type' => 0,
                'ko' => 0,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registerBoard (Request $request) {

        //
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'type' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $type = $request->input('type');

        try {

            $data['id'] = Board::insertGetId([
                'type' => $type,
                'title' => '',
                'content' => '',
                'ko' => 0,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registerSocials (Request $request) {

        //
        $data = [];
        $msg = "";
        $success = false;

        try {

            $data['id'] = Social::insertGetId([
                'type' => 0,
                'link' => '',
                'title' => '',
                'ko' => 0,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registerHistory (Request $request) {

        //
        $data = [];
        $msg = "";
        $success = false;

        try {

            $historyCategory = HistoryCategory::select('id', 'year_start')
                                ->where('ko', '=', 0)
                                ->where('status', '!=', 1)
                                ->orderByDesc('year_start')
                                ->first();

            $data['id'] = History::insertGetId([
                'ref_history_id' => $historyCategory->id,
                'year' => $historyCategory->year_start,
                'month' => '1',
                'ko' => 0,
            ]);

            $data['categoryId'] = $historyCategory->id;

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registehistoryCategory (Request $request) {

        //
        $data = [];
        $msg = "";
        $success = false;

        try {

            $data['id'] = HistoryCategory::insertGetId([
                'content' => '',
                'ko' => 0,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function productAddCertificate (Request $request) {
        
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'productId' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $productId = $request->input('productId');

        try {

            $data['id'] = Certificate::insertGetId([
                'status' => 0,
                'ko' => 0,
            ]);

            ProductCertificate::insert([
                'ref_certificates_id' => $data['id'],
                'ref_products_id' => $productId,
                'ko' => 0,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function productAddSns (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'productId' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $productId = $request->input('productId');

        try {

            $data['id'] = Social::insertGetId([
                                'ref_products_id' => $productId,
                                'type' => 2,
                                'link' => "",
                                'title' => "",
                                'ko' => 0,
                            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registerProcurment (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        try {

            $productProcurementListId = ProductProcurementList::insertGetId([
                'status' => 0,
                'ko' => 0,
            ]);

            ProductProcurement::upsert([
                ['ref_procurementList_id' => $productProcurementListId, 'type' => 0, 'ko' => 0, ],
                ['ref_procurementList_id' => $productProcurementListId, 'type' => 1, 'ko' => 0, ],
                ['ref_procurementList_id' => $productProcurementListId, 'type' => 2, 'ko' => 0, ],
                ['ref_procurementList_id' => $productProcurementListId, 'type' => 3, 'ko' => 0, ],
                ['ref_procurementList_id' => $productProcurementListId, 'type' => 4, 'ko' => 0, ],
            ], [], []);
            
            $data = ProductProcurement::select('id', 'type', 'ref_procurementList_id')
                                        ->where('ref_procurementList_id', '=', $productProcurementListId)
                                        ->first();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registerProcurmentTableRow (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        // procurement id
        // procurement header id [ status : 0 ]

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $id = $request->input('id');
        $type = $request->input('type');

        try {

            // 행이 추가되어야 하는 테이블이 속한 상품의 id 를 통해, 테이블 헤더 조회 후 빈 컬럼을 추가할 계획
            $headerColumns = ProductProcurementTableColumnHeader::select('id')
                                ->where('ref_procurement_id', '=', $id)
                                ->where('status', '!=', 1)
                                ->where('ko', '=', 0)
                                ->get();

            $row = ProductProcurementTableColumn::select('row')
                        ->where('ref_procurement_id', '=', $id)
                        ->where('status', '!=', 1)
                        ->where('ko', '=', 0)
                        ->max('row');

            foreach ($headerColumns as $header_id) {

                ProductProcurementTableColumn::insert([
                    'row' => $row+1,
                    'type' => 0,
                    'ref_table_header_id' => $header_id->id,
                    'ref_procurement_id' => $id,
                    'ref_procurement_type_id' => $type,
                    'content' => '',
                    'ko' => 0,
                ]);

            }

            $data['addColumnId'] = ProductProcurementTableColumn::where('row', '=', $row+1)
            ->where('ref_procurement_id', '=', $id)
            ->where('ref_procurement_type_id', '=', $type)
            ->where('ko', '=', 0)
            ->select('id')
            ->get();

            $data['row'] = $row+1 ;
            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function registerProcurmentTableCol (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        // procurement id
        // procurement header id [ status : 0 ]

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $id = $request->input('id');
        $type = $request->input('type');

        try {

            // 열 추가 시, 헤더 테이블 신규 생성 및 생성된 데이터의 ID 값을 이용하여, 테이블 데이터 내 해당 ID 를 참조하는 컬럼을 생성한다.
            $headerColumnId = ProductProcurementTableColumnHeader::insertGetId([
                'ref_procurement_id' => $id,
                'ko' => 0,
            ]);

            $data['headerId'] = $headerColumnId;

            $row = ProductProcurementTableColumn::select('row')
                        ->where('ref_procurement_id', '=', $id)
                        ->where('status', '!=', 1)
                        ->where('ko', '=', 0)
                        ->get()
                        ->unique('row');

            foreach ($row as $item) {

                ProductProcurementTableColumn::insert([
                    'row' => $item->row,
                    'type' => 0,
                    'ref_table_header_id' => $headerColumnId,
                    'ref_procurement_id' => $id,
                    'ref_procurement_type_id' => $type,
                    'content' => '',
                    'ko' => 0,
                ]);

            }

            $data['addColumnId'] = ProductProcurementTableColumn::where('ref_table_header_id', '=', $headerColumnId)
            ->where('ko', '=', 0)
            ->select('id')->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);


    }

    public function registerProcurmentGuide (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        // procurement id
        // procurement header id [ status : 0 ]

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $id = $request->input('id');
        $type = $request->input('type');
        $guideType = $request->input('guideType');
        
        try {

            $data['id'] = ProductProcurementGuideList::insertGetId([
                'ref_procurement_id' => $id,
                'ref_procurement_type_id' => $type,
                'type' => $guideType,
                'content' => '',
                'ko' => 0,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function productAddCertificateImage (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'file' => 'required',
        ]);

        // procurement id
        // procurement header id [ status : 0 ]

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }
        
        try {

            $id = $request->input('id');
            $file = $request->file('file');

            if(!empty($file)) {

                $random = Str::random(15);
                $originFileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $file->getClientOriginalExtension();
                $ext = explode('.', $originFileName);
                
                $path = $file->storeAs('public/certificates/', $fileName);
                $url = "https://api.bio3s.com/storage/certificates/";

                $changeImageId = File::insertGetId([
                    'type' => "certificates",
                    'ref_type_id' => $id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('ref_type_id', '=', $id)
                ->where('type', '=', 'certificates')
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 1,
                ]);

                $data['fileAddr'] = $url.$fileName;
                $data['origin_filename'] = $fileName;
                

            };
            

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    /* ------------ UPDATE ------------ */
    public function updateProduct(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id             = $request->input('id');
        $main           = $request->input('main');
        $category       = $request->input('category');
        $title          = $request->input('title');
        $tags           = $request->input('tags');
        $selLink        = $request->input('selLink');
        $shortContent   = $request->input('shortContent');
        $file           = $request->file('file');
        $content        = $request->input('content');
        $sns            = json_decode($request->input('sns'), true);
        $faq            = json_decode($request->input('faq'), true);

        try {

            // 상품 내 정보 수정 관련
            Product::where('id', '=', $id)->update([
                'status' => $main,
                'category' => $category,
                'title' => $title,
                'tags' => $tags,
                'sel_link' => $selLink,
                'short_content' => $shortContent,
                'content' => $content,
            ]);

            // 상품의 귀속된 소셜 내용 수정 관련
            if(!empty($sns)) {
                
                foreach($sns as $item) {

                    Social::where('id', '=', $item['id'])
                            ->where('ref_products_id', '=', $id)
                            ->update([
                                'link' => $item['link'],
                                'title' => $item['title'],
                            ]);

                }
            }

            // 자주 묻는 질의/응답 수정 관련
            if(!empty($faq)) {
                
                foreach($faq as $item) {

                    Faq::where('id', '=', $item['id'])
                            ->where('ref_products_id', '=', $id)
                            ->update([
                                'content' => $item['content'],
                                'title' => $item['title'],
                            ]);

                }
            }

            // 상품 이미지 변경 관련
            if($request->hasFile('file')) {

                $random = Str::random(15);
                $originFileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $file->getClientOriginalExtension();
                $ext = explode('.', $originFileName); 
                
                $path = $file->storeAs('public/product/', $fileName);
                $url = "https://api.bio3s.com/storage/product/";

                $changeImageId = File::insertGetId([
                    'type' => "products",
                    'ref_type_id' => $id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('type', '=', 'products')
                ->where('ref_type_id', '=', $id)
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 1,
                ]);
                
            };

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }
    
    public function updateProductInSocialsImage(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'file' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        try {

            // 상품 이미지 변경 관련
            if($request->hasFile('file')) {

                $id = $request->input('id');
                $file = $request->file('file');

                $random = Str::random(15);
                $originFileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $file->getClientOriginalExtension();
                $ext = explode('.', $originFileName); 
                
                $path = $file->storeAs('public/socials/', $fileName);
                $url = "https://api.bio3s.com/storage/socials/";

                $changeImageId = File::insertGetId([
                    'type' => "socials",
                    'ref_type_id' => $id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('type', '=', 'socials')
                ->where('ref_type_id', '=', $id)
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 1,
                ]);
                
            };

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
    }

    public function updatePopup(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id         = $request->input('id');
        $title      = $request->input('title');
        $link       = $request->input('link');
        $file       = $request->file('file');

        try {

            // 상품 내 정보 수정 관련
            Popup::where('id', '=', $id)->update([
                'title' => $title,
                'link' => $link,
                'ko' => 0,
            ]);

            // 상품 이미지 변경 관련
            if($request->hasFile('file')) {

                $random = Str::random(15);
                $originFileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $file->getClientOriginalExtension();
                $ext = explode('.', $originFileName); 
                
                $path = $file->storeAs('public/popups/', $fileName);
                $url = "https://api.bio3s.com/storage/popups/";

                $changeImageId = File::insertGetId([
                    'type' => "popups",
                    'ref_type_id' => $id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('type', '=', 'popups')
                ->where('ref_type_id', '=', $id)
                ->where('type', '=', 'popups')
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 1,
                ]);
                
            };

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function updateQna(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'qna_id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id = $request->input('qna_id');

        try {

            // 상품 내 정보 수정 관련
            Service::where('id', '=', $id)->update([
                'status' => 1,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function updateBanner(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id = $request->input('id');
        $title = $request->input('title');
        $content = nl2br($request->input('content'));
        $fileExt = $request->input('ext');
        $file = $request->file('file');

        try {

            Banner::where('id', '=', $id)->update([
                'title' => $title,
                'content' => $content,
                'type' => $fileExt,
            ]);

            if($request->hasFile('file')) {

                $random = Str::random(15);
                $originFileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $file->getClientOriginalExtension();
                $ext = explode('.', $originFileName); 
                
                $path = $file->storeAs('public/banners/', $fileName);
                $url = "https://api.bio3s.com/storage/banners/";

                $changeImageId = File::insertGetId([
                    'type' => "banners",
                    'ref_type_id' => $id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[count($ext)-1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('type', '=', 'banners')
                ->where('ref_type_id', '=', $id)
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 1,
                ]);
            };

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function updateHistory(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id         = $request->input('id');
        $ref_id     = $request->input('historyCategoryId');
        $year       = $request->input('year');
        $month      = $request->input('month');
        $content    = $request->input('content');

        try {

            History::where('id', '=', $id)->update([
                'ref_history_id' => $ref_id,
                'year' => $year,
                'month' => $month,
                'content' => $content,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function updatePatent(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id                 = $request->input('id');
        $title              = $request->input('title');
        $number             = $request->input('number');
        $type               = $request->input('type');
        $register_date      = $request->input('register_date');
        $country            = $request->input('country');
        $writer             = $request->input('writer');

        try {

            Patent::where('id', '=', $id)->update([
                'title'         => $title,
                'number'        => $number,
                'type'          => $type,
                'register_date' => $register_date,
                'country'       => $country,
                'writer'        => $writer,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function updateCertificate(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id             = $request->input('id');
        $type           = $request->input('type');
        $register_date  = $request->input('register_date');
        $doc_status     = $request->input('doc_status');
        $number         = $request->input('number');
        $content        = $request->input('content');

        try {

            Certificate::where('id', '=', $id)->update([
                'type'          => $type,
                'register_date' => $register_date,
                'doc_status'    => $doc_status,
                'number'        => $number,
                'content'       => $content,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function updateGallery(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id             = $request->input('id');
        $content        = $request->input('content');
        $registerDate   = $request->input('registerDate');
        $docType        = $request->input('docType');
        $file           = $request->file('file');

        try {

            Gallery::where('id', '=', $id)->update([
                'content' => $content,
                'register_date' => $registerDate,
                'type' => $docType,
            ]);

            if($request->hasFile('file')) {

                $random = Str::random(15);
                $originFileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $file->getClientOriginalExtension();

                $ext = explode('.', $originFileName); 
                
                $path = $file->storeAs('public/gallery/', $fileName);
                $url = "https://api.bio3s.com/storage/gallery/";

                $changeImageId = File::insertGetId([
                    'type' => "galleries",
                    'ref_type_id' => $id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('type', '=', 'galleries')
                ->where('ref_type_id', '=', $id)
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 2,
                ]);
            };

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function updateBoard(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id      = $request->input('id');
        $type    = $request->input('type');
        $title   = $request->input('title');
        $content = $request->input('content');
        $files   = $request->file('files');

        try {

            Board::where('id', '=', $id)->update([
                'status' => $type,
                'title' => $title,
                'content' => $content,
            ]);

            if(!empty($files)) {

                foreach ($files as $file) {

                    $random = Str::random(15);
                    $originFileName = $file->getClientOriginalName();
                    $fileSize = $file->getSize();
                    $fileName = (now()->timestamp) . $random .'.' . $file->getClientOriginalExtension();

                    $ext = explode('.', $originFileName); 
                    
                    $path = $file->storeAs('public/boards/', $fileName);
                    $url = "https://api.bio3s.com/storage/boards/";

                    File::insert([
                        'type' => "boards",
                        'ref_type_id' => $id,
                        'filename' => $fileName,
                        'origin_filename' => $originFileName,
                        'filesize' => $fileSize,
                        'ext' => $ext[1],
                        'fileAddr' => $url.$fileName,
                    ]);
                    
                };

            };

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function updateSocials(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id      = $request->input('id');
        $link    = $request->input('link');
        $title   = $request->input('title');
        $docType = $request->input('docType');
        $file   = $request->file('file');

        try {

            Social::where('id', '=', $id)->update([
                'link' => $link,
                'title' => $title,
                'type' => $docType,
            ]);

            if(!empty($file)) {

                $random = Str::random(15);
                $originFileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $file->getClientOriginalExtension();
                $ext = explode('.', $originFileName); 
                
                $path = $file->storeAs('public/socials/', $fileName);
                $url = "https://api.bio3s.com/storage/socials/";

                $changeImageId = File::insertGetId([
                    'type' => "socials",
                    'ref_type_id' => $id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('type', '=', 'socials')
                ->where('ref_type_id', '=', $id)
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 1,
                ]);

            };

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function updateHistoryCategory(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id         = $request->input('id');
        $year_start = $request->input('year_start');
        $year_end   = $request->input('year_end');
        $content    = $request->input('content');

        try {

            HistoryCategory::where('id', '=', $id)->update([
                'year_start' => $year_start,
                'year_end' => $year_end,
                'content' => $content,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function updateProcurement(Request $request) {

        $data = [];
        $msg = "";
        $success = false;
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id                    = $request->input('id');
        $type                  = $request->input('type');
        $ref_id                = $request->input('ref_id');
        $status                = $request->input('status');
        $title                 = $request->input('title');
        $tags                  = $request->input('tags');
        $shortContent          = $request->input('shortContent');
        $deliverGuide          = $request->input('deliverGuide');

        $labelImageFirst       = $request->input('labelFirstText');
        $labelImageSecond      = $request->input('labelSecondText');
        $labelImageThird       = $request->input('labelThirdText');

        $table                 = $request->input('table');
        $tableHeader           = $request->input('tableHeader');
        $selKindList           = $request->input('selKindList');
        $selOptionList         = $request->input('selOptionList');
        $labelExList           = $request->input('labelExList');

        // 
        $productImageFile      = $request->file('productImageFile');
        $contractFile          = $request->file('contractFile');
        $centerImageFile       = $request->file('centerImageFile');

        $labelImageFirstFile   = $request->file('labelImageFirstFile');
        $labelImageSecondFile  = $request->file('labelImageSecondFile');
        $labelImageThirdFile   = $request->file('labelImageThirdFile');

        try {

            ProductProcurement::where('id', '=', $id)
                                ->update([
                                    'tags' => $tags,
                                    'title' => $title,
                                    'short_content' => $shortContent,
                                    'deliverGuide' => $deliverGuide,
                                    'status' => $status,
                                ]);

                                
            // 
            // 3: 사진1, 4: 사진2, 5: 사진3, 
            // 6: detail center image,
            // 7: contract_document_file
            
            // 구매방법
            if(!empty($selKindList)) {

                foreach ($selKindList as $item) {

                    $temp = json_decode($item);

                    ProductProcurementGuideList::where('id', '=', $temp->id)
                                                    ->update([
                                                        'content' => $temp->content,
                                                    ]);

                }
            }
            // 구매 옵션
            if(!empty($selOptionList)) {

                foreach ($selOptionList as $item) {

                    $temp = json_decode($item);

                    ProductProcurementGuideList::where('id', '=', $temp->id)
                                                    ->update([
                                                        'content' => $temp->content,
                                                    ]);

                }
            }
            // 라벨 홍보 방법
            if(!empty($labelExList)) {

                foreach ($labelExList as $item) {

                    $temp = json_decode($item);

                    ProductProcurementGuideList::where('id', '=', $temp->id)
                                                    ->update([
                                                        'content' => $temp->content,
                                                    ]);

                }
            }

            ProductProcurementGuideList::updateOrInsert(
                [
                    'ref_procurement_id' => $ref_id,
                    'ref_procurement_type_id' => $type,
                    'type' => 3,
                    'ko' => 0,
                ], 
                [
                    'content' => $labelImageFirst,
                ]
            );

            ProductProcurementGuideList::updateOrInsert(
                [
                    'ref_procurement_id' => $ref_id,
                    'ref_procurement_type_id' => $type,
                    'type' => 4,
                    'ko' => 0,
                ], 
                [
                    'content' => $labelImageSecond,
                ]
            );

            ProductProcurementGuideList::updateOrInsert(
                [
                    'ref_procurement_id' => $ref_id,
                    'ref_procurement_type_id' => $type,
                    'type' => 5,
                    'ko' => 0,
                ], 
                [
                    'content' => $labelImageThird,
                ]
            );
            

            // 테이블 헤더
            if(!empty($tableHeader)) {
            
                foreach ($tableHeader as $item) {

                    $temp = json_decode($item);

                    ProductProcurementTableColumnHeader::where('id', '=', $temp->id)
                                                            ->update([
                                                                'content' => $temp->content,
                                                            ]);
                }

            } 
            // 테이블 데이터
            if(!empty($table)) {

                foreach ($table as $key => $item) {
                    
                    $ele = json_decode($item);

                    foreach ($ele as $col) {

                        $tableColumnType = 0;

                        if( $col->type == true ) {

                            $tableColumnType = 1;

                        }

                        ProductProcurementTableColumn::where('id', '=', $col->id)
                                                    ->update([
                                                        'type' => $tableColumnType,
                                                        'content' => $col->content,
                                                    ]);

                    }
                    

                }

            }
            
            //
            if($request->hasFile('productImageFile')) {

                $random = Str::random(15);
                $originFileName = $productImageFile->getClientOriginalName();
                $fileSize = $productImageFile->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $productImageFile->getClientOriginalExtension();
                $ext = explode('.', $originFileName); 
                
                $path = $productImageFile->storeAs('public/productProcurement/', $fileName);
                $url = "https://api.bio3s.com/storage/productProcurement/";

                $changeImageId = File::insertGetId([
                    'type' => "productProcurement",
                    'ref_type_id' => $id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[count($ext)-1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('type', '=', 'productProcurement')
                ->where('ref_type_id', '=', $id)
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 1,
                ]);
            };
            //
            if($request->hasFile('contractFile')) {

                // productProcurementGuideList
                $guideId = ProductProcurementGuideList::select('id')
                                            ->where('ref_procurement_id', '=', $ref_id)
                                            ->where('ref_procurement_type_id', '=', $type)
                                            ->where('type', '=', 7)
                                            ->where('ko', '=', 0)
                                            ->where('status', '=', 0)
                                            ->first();

                if(empty($guideId)) {

                    $guideId = ProductProcurementGuideList::insertGetId([
                        'ref_procurement_id' => $ref_id,
                        'ref_procurement_type_id' => $type,
                        'type' => 7,
                        'ko' => 0,
                    ]);

                }

                $random = Str::random(15);
                $originFileName = $contractFile->getClientOriginalName();
                $fileSize = $contractFile->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $contractFile->getClientOriginalExtension();
                $ext = explode('.', $originFileName); 
                
                $path = $contractFile->storeAs('public/productProcurementGuideList/', $fileName);
                $url = "https://api.bio3s.com/storage/productProcurementGuideList/";

                $changeImageId = File::insertGetId([
                    'type' => "productProcurementGuideList",
                    'ref_type_id' => (empty($guideId)) ? $guideId : $guideId->id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[count($ext)-1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('type', '=', 'productProcurementGuideList')
                ->where('ref_type_id', '=', (empty($guideId)) ? $guideId : $guideId->id)
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 1,
                ]);
                
                
            }

            if($request->hasFile('centerImageFile')) {

                // productProcurementGuideList
                $guideId = ProductProcurementGuideList::select('id')
                                            ->where('ref_procurement_id', '=', $ref_id)
                                            ->where('ref_procurement_type_id', '=', $type)
                                            ->where('type', '=', 6)
                                            ->where('ko', '=', 0)
                                            ->where('status', '=', 0)
                                            ->first();

                                            $data['asdsad'] = $guideId;

                if(empty($guideId)) {

                    $guideId = ProductProcurementGuideList::insertGetId([
                        'ref_procurement_id' => $ref_id,
                        'ref_procurement_type_id' => $type,
                        'type' => 6,
                        'ko' => 0,
                    ]);

                }

                $random = Str::random(15);
                $originFileName = $centerImageFile->getClientOriginalName();
                $fileSize = $centerImageFile->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $centerImageFile->getClientOriginalExtension();
                $ext = explode('.', $originFileName); 
                
                $path = $centerImageFile->storeAs('public/productProcurementGuideList/', $fileName);
                $url = "https://api.bio3s.com/storage/productProcurementGuideList/";

                $changeImageId = File::insertGetId([
                    'type' => "productProcurementGuideList",
                    'ref_type_id' => (empty($guideId)) ? $guideId : $guideId->id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[count($ext)-1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('type', '=', 'productProcurementGuideList')
                ->where('ref_type_id', '=', (empty($guideId)) ? $guideId : $guideId->id,)
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 1,
                ]);

            }

            if($request->hasFile('labelImageFirstFile')) {

                // productProcurementGuideList
                $guideId = ProductProcurementGuideList::select('id')
                                            ->where('ref_procurement_id', '=', $ref_id)
                                            ->where('ref_procurement_type_id', '=', $type)
                                            ->where('type', '=', 3)
                                            ->where('status', '=', 0)
                                            ->where('ko', '=', 0)
                                            ->first();

                if(empty($guideId)) {

                    $guideId = ProductProcurementGuideList::insertGetId([
                        'ref_procurement_id' => $ref_id,
                        'ref_procurement_type_id' => $type,
                        'type' => 3,
                        'ko' => 0,
                    ]);

                }


                $random = Str::random(15);
                $originFileName = $labelImageFirstFile->getClientOriginalName();
                $fileSize = $labelImageFirstFile->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $labelImageFirstFile->getClientOriginalExtension();
                $ext = explode('.', $originFileName); 
                
                $path = $labelImageFirstFile->storeAs('public/productProcurementGuideList/', $fileName);
                $url = "https://api.bio3s.com/storage/productProcurementGuideList/";

                $changeImageId = File::insertGetId([
                    'type' => "productProcurementGuideList",
                    'ref_type_id' => (empty($guideId)) ? $guideId : $guideId->id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[count($ext)-1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('type', '=', 'productProcurementGuideList')
                ->where('ref_type_id', '=', (empty($guideId)) ? $guideId : $guideId->id)
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 1,
                ]);

            }

            if($request->hasFile('labelImageSecondFile')) {
                // productProcurementGuideList
                $guideId = ProductProcurementGuideList::select('id')
                                            ->where('ref_procurement_id', '=', $ref_id)
                                            ->where('ref_procurement_type_id', '=', $type)
                                            ->where('type', '=', 4)
                                            ->where('status', '=', 0)
                                            ->where('ko', '=', 0)
                                            ->first();
                if(empty($guideId)) {
                    $guideId = ProductProcurementGuideList::insertGetId([
                        'ref_procurement_id' => $ref_id,
                        'ref_procurement_type_id' => $type,
                        'type' => 4,
                        'ko' => 0,
                    ]);

                }

                $random = Str::random(15);
                $originFileName = $labelImageSecondFile->getClientOriginalName();
                $fileSize = $labelImageSecondFile->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $labelImageSecondFile->getClientOriginalExtension();
                $ext = explode('.', $originFileName); 
                
                $path = $labelImageSecondFile->storeAs('public/productProcurementGuideList/', $fileName);
                $url = "https://api.bio3s.com/storage/productProcurementGuideList/";

                $data['test'] = $guideId;
                $changeImageId = File::insertGetId([
                    'type' => "productProcurementGuideList",
                    'ref_type_id' => (empty($guideId)) ? $guideId : $guideId->id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[count($ext)-1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('type', '=', 'productProcurementGuideList')
                ->where('ref_type_id', '=', (empty($guideId)) ? $guideId : $guideId->id)
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 1,
                ]);
            }

            if($request->hasFile('labelImageThirdFile')) {

                // productProcurementGuideList
                $guideId = ProductProcurementGuideList::select('id')
                                            ->where('ref_procurement_id', '=', $ref_id)
                                            ->where('ref_procurement_type_id', '=', $type)
                                            ->where('type', '=', 5)
                                            ->where('status', '=', 0)
                                            ->where('ko', '=', 0)
                                            ->first();

                if(empty($guideId)) {

                    $guideId = ProductProcurementGuideList::insertGetId([
                        'ref_procurement_id' => $ref_id,
                        'ref_procurement_type_id' => $type,
                        'type' => 5,
                        'ko' => 0,
                    ]);

                }


                $random = Str::random(15);
                $originFileName = $labelImageThirdFile->getClientOriginalName();
                $fileSize = $labelImageThirdFile->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $labelImageThirdFile->getClientOriginalExtension();
                $ext = explode('.', $originFileName); 
                
                $path = $labelImageThirdFile->storeAs('public/productProcurementGuideList/', $fileName);
                $url = "https://api.bio3s.com/storage/productProcurementGuideList/";

                $changeImageId = File::insertGetId([
                    'type' => "productProcurementGuideList",
                    'ref_type_id' => (empty($guideId)) ? $guideId : $guideId->id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[count($ext)-1],
                    'fileAddr' => $url.$fileName,
                ]);

                File::where('type', '=', 'productProcurementGuideList')
                ->where('ref_type_id', '=', (empty($guideId)) ? $guideId : $guideId->id)
                ->where('id', '!=', $changeImageId)
                ->update([
                    'status' => 1,
                ]);
            }

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        // $data = json_decode($tableHeader[0]);

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' =>  $data
        ]);

    }
    

    /* ------------ DELETE ------------ */
    public function deleteBanner(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'banner_id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }
        
        $bannerId = $request->input('banner_id');
        $fileId =  $request->input('file_id');

        try {

            Banner::where('id', '=', $bannerId)
                    ->update([
                        'status' => 1
                    ]);
                
            if(!empty($fileId)) {
                File::where('type', '=', 'banners')
                        ->where('id', '=', $fileId)
                        ->update([
                            'status' => '1'
                        ]);
            }

            $success = true;

        } catch (\Exception $e) {

            // Banner::where()
            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
    }

    public function deletePopup(Request $request) {
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'popup_id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        try {

            $id = $request->input('popup_id');

            Popup::where('id', '=', $id)->update([
                'status' => '1',
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
    }
    
    public function deleteQna(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'qna_id'      => 'required',
        ]);

        if($validator->fails()) {
            $msg = "NOT VALIDATION";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id = $request->input('qna_id');

        try {

            Service::where('id', '=', $id)
                ->update([
                    'status' => '2',
                ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
    }

    public function deleteHistory(Request $request) {
        $data = [];
        $msg = "";
        $success = false;

        $history_id = $request->input('history_id');
        try {

            History::where('id', '=', $history_id)->update([
                'status' => 1,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
    }

    public function deleteProduct(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $productId = $request->input('product_id');

        try {

            Product::where('id', '=', $productId)
                    ->update([
                        'status' => 2,
                    ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }
    
    public function deletePatent(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        try {

            $id = $request->input('id');
            
            Patent::where('id', '=', $id)->update([
                'status' => 1,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
    }

    public function deleteCertificate(Request $request) {
        
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        try {

            $id = $request->input('id');

            Certificate::where('id', '=', $id)->update([
                'status' => 1,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
    }
    
    public function deleteGallery(Request $request) {
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id = $request->input('id');

        try {

            Gallery::where('id', '=', $id)->update([
                'status' => 1,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
    }
    
    //  * function delete not using
    public function deleteNews(Request $request) {
        $data = [];
        $msg = "";
        $success = false;

        try {

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
    }
    
    //  * function delete not using
    public function deleteNotice(Request $request) {
        $data = [];
        $msg = "";
        $success = false;

        try {

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
    }
    
    public function deleteSns(Request $request) {
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id = $request->input('id');

        try {

            Social::where('id', '=', $id)->update([
                'status' => 1,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
    }

    public function deleteFaq(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id'      => 'required',
        ]);

        if($validator->fails()) {
            $msg = "NOT VALIDATION";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id = $request->input('id');

        try {

            Faq::where('id', '=', $id)
                ->update([
                    'status' => '1',
                ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
        
    }
    
    public function productDeleteCertificate (Request $request) {
        
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'productId' => 'required',
            'certficateId' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $productId = $request->input('productId');
        $certficateId = $request->input('certficateId');

        try {

            ProductCertificate::where('ref_products_id', '=', $productId)
            ->where('ref_certificates_id', '=', $certficateId)
            ->where('status', '=', 0)
            ->where('ko', '=', 0)
            ->update([
                'status' => 1,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
        
    }

    public function productDeleteSns (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'productId' => 'required',
            'snsId' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $productId = $request->input('productId');
        $snsId = $request->input('snsId');

        try {

            Social::where('ref_products_id', '=', $productId)
            ->where('id', '=', $snsId)
            ->where('status', '=', 0)
            ->where('ko', '=', 0)
            ->update([
                'status' => 1,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function deleteBoardFile (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $id = $request->input('id');

        try {

            File::where('type', '=', 'boards')
                ->where('id', '=', $id)
                ->update([
                    'status' => 1,
                ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function deleteBoard (Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $id = $request->input('id');

        try {

            Board::where('id', '=', $id)
                ->update([
                    'status' => 2,
                ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function deleteHistoryCategory (Request $request) {
        
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $id = $request->input('id');

        try {

            HistoryCategory::where('id', '=', $id)
                ->update([
                    'status' => 1,
                ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function deleteTableRow (Request $request) {
        
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $id = $request->input('id');

        try {

            ProductProcurementTableColumn::where('row', '=', $id)
                ->where('ko', '=', 0)
                ->update([
                    'status' => 1,
                ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function deleteTableCol (Request $request) {
        
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $id = $request->input('id');

        try {

            ProductProcurementTableColumnHeader::where('id', $id)->update([
                'status' => 1,
            ]);

            ProductProcurementTableColumn::where('ref_table_header_id', '=', $id)
                ->where('ko', '=', 0)
                ->update([
                    'status' => 1,
                ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function deleteGuide (Request $request) {
        
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $id = $request->input('id');
        $type = $request->input('type');

        try {

            ProductProcurementGuideList::where('id', '=', $id)
            ->where('ko', '=', 0)
            ->where('type', '=', $type)
            ->update([
                'status' => 1,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function deleteProcurment (Request $request) {
        
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $id = $request->input('id');

        try {

            ProductProcurementList::where('id', '=', $id)
            ->where('ko', '=', 0)
            ->update([
                'status' => 1,
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    public function deleteCertificateImage (Request $request) {
        
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        // product id
        $id = $request->input('id');

        try {

            Certificate::where('id', '=', $id)
            ->where('ko', '=', 0)
            ->update(['status' => 1]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    /* ------------ Editor Image Save ------------ */
    public function tableSearch(Request $request) {
        
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'type'      => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        try {

            $keyword = $request->input('keyword');
            $type = $request->input('type');

            $table;
            
            switch ($type) {

                case 'BANNERS':
                    $table = Banner::query();
                    $table->leftJoin('files', function($leftJoin) {
                        $leftJoin->on('banners.id', '=', 'files.ref_type_id')
                                ->where('files.type', '=', 'banners')
                                ->where('files.status', '=', 0);
                    });
                    $table->select('banners.id', 'banners.type', 'banners.title', 'banners.content', 'files.id as file_id' ,'files.fileAddr', 'files.ext', 'banners.created_at');
                    $table->where('banners.status', '=', 0);
                    $table->where('banners.ko', '=', 0);

                    if(!empty($keyword)) $table->where('banners.title', 'like', '%'.$keyword.'%');

                    $data = $table->get();

                    break;

                case 'POPUPS':
                    $table = Popup::query();

                    $table->select('*')->where('popups.status', '!=', 1);
                    $table->where('popups.ko', '=', 0);

                    if(!empty($keyword)) $table->where('popups.title', 'like', '%'.$keyword.'%');

                    $table->orderByDesc('popups.created_at');

                    $data = $table->get();

                    break;

                case 'INQUIRY':
                    $table = Service::query();

                    $table->select('services.id', 'services.status', 'services.phone', 'services.name', 'services.email', 'services.content', 'services.company', 'services.category', 'services.created_at')
                    ->where('services.status', '!=', 2);
                    $table->where('services.ko', '=', 0);

                    if(!empty($keyword)) $table->where('services.content', 'like', '%'.$keyword.'%');

                    $table->orderByDesc('services.id');

                    $data = $table->get();

                    break;

                case 'HISTORIES':

                    $table = History::query();

                    $table->leftJoin('historyCategories', function($leftJoin) {
                        $leftJoin->on('histories.ref_history_id', '=', 'historyCategories.id')
                                    ->where('historyCategories.status', '!=', 1);
                    });
                    $table->select(
                        'histories.id', 
                        'historyCategories.id as ref_id',
                        'historyCategories.content as type',
                        'historyCategories.year_start',
                        'historyCategories.year_end',
                        'histories.year', 
                        'histories.month', 
                        'histories.content', 
                        'histories.created_at'
                    );

                    $table->where('histories.status', '!=', 1);
                    $table->where('histories.ko', '=', 0);
                    

                    if(!empty($keyword)) $table->where('histories.content', 'like', '%'.$keyword.'%');

                    $table->orderByDesc('histories.year');
                    $table->orderByDesc('histories.month');

                    $data = $table->get();

                    break;

                case 'PRODUCTS':
                    
                    $table = Product::query();

                    $table->leftJoin('files', function($leftJoin) {
                        $leftJoin->on('products.id', '=', 'files.ref_type_id')
                                ->where('files.type', '=', 'products')
                                ->where('files.status', '=', 0);
                    });
                    $table->select('products.id', 'products.status', 'products.category' ,'products.title', 'products.content', 'products.tags', 'products.sel_link', 'products.short_content', 'files.fileAddr', 'products.created_at');
                    $table->where('products.status', '!=', 2);
                    $table->where('products.ko', '=', 0);

                    if(!empty($keyword)) $table->where('products.title', 'like', '%'.$keyword.'%');
                    $table->orderByDesc('products.created_at');

                    $data = $table->get();
        
                    $success = true;

                    break;

                case 'PATENTS':

                    $table = Patent::query();
                    $table->where('patents.ko', '=', 0);
                    $table->where('patents.status', '!=', 2);

                    $table->select("*");

                    if(!empty($keyword)) $table->where('patents.title', 'like', '%'.$keyword.'%');

                    $table->orderByDesc('patents.created_at');
                    $data = $table->get();

                    break;

                case 'INQUIRY':

                    $table = Service::query();

                    $table->select
                    (
                        'id', 
                        'status', 
                        'phone', 
                        'name', 
                        'email', 
                        'content', 
                        'company', 
                        'category', 
                        'created_at'
                    );
                    $table->where('services.status', '!=', 2);
                    $table->where('services.ko', '=', 0);

                    if(!empty($keyword)) $table->where('services.content', 'like', '%'.$keyword.'%');
                    
                    $data = $table->get();

                    break;

                case 'CERTIFICATES':

                    $table = Certificate::query();
                    $table->where('certificates.status', '!=', 2);
                    $table->where('certificates.ko', '=', 0);
                    $table->select('*');

                    if(!empty($keyword)) $table->where('certificates.content', 'like', '%'.$keyword.'%');

                    $data = $table->get();

                    break;
                    
                case 'ESG':

                    $table = Gallery::query();

                    $table->leftJoin('files', function($leftJoin) {
                        $leftJoin->on('galleries.id', '=', 'files.ref_type_id')
                                ->where('files.type', '=', 'galleries')
                                ->where('files.status', '=', 0);
                    });
        
                    $table->select('galleries.id', 'galleries.type', 'galleries.content', 'files.fileAddr', 'files.origin_filename', 'files.ext', 'galleries.register_date', 'galleries.created_at');
                    $table->where('galleries.status', '=', 0);
                    $table->where('galleries.ko', '=', 0);

                    if(!empty($keyword)) $table->where('galleries.content', 'like', '%'.$keyword.'%');

                    $table->orderByDesc('galleries.content');
                    $data = $table->get();

                    break;

                case 'NEWS':

                    $table = Board::query();

                    $table->where('boards.status', '=', 0);
                    $table->where('boards.ko', '=', 0);
                    $table->where('boards.type', '=', 0);

                    if(!empty($keyword)) $table->where('boards.content', 'like', '%'.$keyword.'%');

                    $table->orderByDesc('boards.content');
                    $data = $table->get();

                    break;

                case 'NOTICE':

                    $table = Board::query();

                    $table->where('boards.status', '=', 0);
                    $table->where('boards.ko', '=', 0);
                    $table->where('boards.type', '=', 1);

                    if(!empty($keyword)) $table->where('boards.title', 'like', '%'.$keyword.'%');

                    $table->orderByDesc('boards.title');
                    $data = $table->get();

                    break;

                case 'SNS':

                    $table = Social::query();

                    $table->leftJoin('files', function($leftJoin) {
                        $leftJoin->on('socials.id', '=', 'files.ref_type_id')
                                ->where('files.type', '=', 'socials')
                                ->where('files.status', '=', 0);
                    });

                    $table->select('socials.id', 'socials.title', 'socials.link', 'files.fileAddr', 'files.origin_filename', 'files.ext');
                    $table->where('socials.status', '=', 0);                    
                    $table->where('socials.ko', '=', 0);

                    if(!empty($keyword)) $table->where('socials.title', 'like', '%'.$keyword.'%');

                    $data = $table->get();

                    break;

                case 'PROCUREMENT':
                    
                    $productProcurementList = ProductProcurementList::query();

                    $productProcurementList->select('id')->where('status', '!=', 1)->get();

                    $query = ProductProcurement::query();

                    $query->leftJoin('files', function($leftJoin) {
                        $leftJoin->on('productProcurement.id', '=', 'files.ref_type_id')
                                ->where('files.type', '=', 'productProcurement')
                                ->where('files.status', '=', 0);
                    });

                    $query->select(
                        'productProcurement.id',
                        'productProcurement.status',
                        'productProcurement.ref_procurementList_id',
                        'productProcurement.type',
                        'productProcurement.title', 
                        'productProcurement.tags', 
                        'productProcurement.created_at',
                        'files.fileAddr'
                    );

                    $query->where('productProcurement.status', '!=', 1);

                    $query->where('productProcurement.ko', '=', 0);

                    $query->whereIn('productProcurement.ref_procurementList_id', $productProcurementList);

                    if(!empty($keyword)) $query->where('productProcurement.title', 'like', '%'.$keyword.'%');

                    $query->groupBy('productProcurement.ref_procurementList_id');

                    $query->orderByDesc('productProcurement.id');

                    $data = $query->get();
                    
                    break;
                
                default:

                    $success = false;
                    $msg = "Not Matching Type";

                    break;
            }
            
            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    /* ------------ Editor Image Save ------------ */
    public function editorImageSave(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
            'image' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        try {

            $id   = $request->input('id');
            $type = $request->input('type');
            $file = $request->file('image');

            if($request->hasFile('image')) {

                $random = Str::random(15);
                $originFileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileName = (now()->timestamp) . $random .'.' . $file->getClientOriginalExtension();
                $ext = explode('.', $originFileName); 
                
                $path = $file->storeAs('public/editor/'.$type.'/', $fileName);
                $url = "https://api.bio3s.com/storage/editor/".$type."/";

                File::insert([
                    'type' => $type,
                    'ref_type_id' => $id,
                    'filename' => $fileName,
                    'origin_filename' => $originFileName,
                    'filesize' => $fileSize,
                    'ext' => $ext[1],
                    'fileAddr' => $url.$fileName,
                ]);
                $data['url'] = $url.$fileName;
            };

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);


    }
    
    
    /* LOGIN AND TOKEN */

    // Admin Login
    public function login(Request $request){

        $data = [];
        $msg = "";
        $success = false;

        $email = $request->input('id');
        $password = $request->input('password');

        // User Check
        $user = User::where('email', $email)->first();
        if($user) {
            if(password_verify($password, $user->password)) {
                try {
                    $data['user'] = $user->email;
                    $data['token'] = $this->createToken($email, $password);
                    $success = true;
                } catch (\Exception $e) {
                    $msg = "일시적인 오류가 발생했습니다. [ token error ]";
                }
            } else {
                $msg = "아이디 혹은 패스워드 정보가 올바르지 않습니다.";
            }
        } else {
            $msg = "아이디 혹은 패스워드 정보가 올바르지 않습니다.";
        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);
    }

    // Create Token 
    private function createToken($email, $password) {
        // access_token, refresh_token
        $response = Http::asForm()->post('https://api.bio3s.com/oauth/token', [
            'grant_type' => 'password',
            'client_id' => '2',
            'client_secret' => 'iF93R2RNLgtisrJcWacLnWC6L31N5JHmzsxoQ9yq',
            'username' => $email,
            'password' => $password,
            'scope' => '*',
        ]);
        return $response->json();
    }

    // Refresh Token
    public function refreshToken(Request $request) {
        
        $data = $request->only('refresh_token');

        $response = Http::asForm()->post('https://api.bio3s.com/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => '2',
            'client_secret' => 'iF93R2RNLgtisrJcWacLnWC6L31N5JHmzsxoQ9yq',
            'refresh_token' => $data['refresh_token'],
            'scope' => '*',
        ]);
        return $response->json();
    }
    
}
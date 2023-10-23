<?php
// Real

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
use App\Models\ProductProcurement;
use App\Models\ProductProcurementList;
use App\Models\ProductProcurementGuideList;
use App\Models\ProductProcurementTableColumn;
use App\Models\ProductProcurementTableColumnHeader;


class ClientControllerEn extends Controller
{
    
    // select 메인
    public function main(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        // Banner : image Link, video Link, Text
        // Product : image Link, Title, Tags
        // Board : Title, Content, Register Date

        try {

            $data['banners'] = Banner::leftJoin('files', function($leftJoin) {
                                            $leftJoin->on('banners.id', '=', 'files.ref_type_id')
                                                    ->where('files.type', '=', 'banners')
                                                    ->where('files.status', '=', 0);
                                        })
                                        ->select('banners.type', 'banners.title', 'banners.content', 'files.fileAddr')
                                        ->where('banners.status', '=', 0)
                                        ->where('banners.ko', '=', 1)
                                        ->where('files.fileAddr', '!=', '')
                                        ->get();


            $data['products'] = Product::leftJoin('files', function($leftJoin) {
                                            $leftJoin->on('products.id', '=', 'files.ref_type_id')
                                                    ->where('files.type', '=', 'products')
                                                    ->where('files.status', '=', 0);
                                        })
                                        ->select('products.id', 'products.title', 'products.tags', 'files.fileAddr')
                                        ->where('products.status', '=', 1)
                                        ->where('products.ko', '=', 1)
                                        ->orderByDesc('products.created_at')
                                        ->get();

            $data['boards'] = Board::select('id', 'type', 'title', 'content', 'created_at')
                                ->where('status', '!=', 2)
                                ->where('ko', '=', 1)
                                ->orderByDesc('created_at')
                                ->limit(10)
                                ->get();

            $data['popups'] = Popup::leftJoin('files', function($leftJoin) {
                $leftJoin->on('popups.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'popups')
                        ->where('files.status', '=', 0);
            })
            ->select('popups.link', 'popups.title', 'files.fileAddr')
            ->where('popups.status', '!=', 1)
            ->where('popups.ko', '=', 1)
            ->orderByDesc('popups.created_at')
            ->get();

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // select 연혁
    public function histories(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        // year : 등록된 연혁 중 연도만 중복을 제거한 후, 조회
        // history : 등록된 연혁 조회
        // category : 범위 제공

        try {

            $data['year'] = History::select('ref_history_id as ref_id', 'year')
                            ->where('ko', '=', 1)
                            ->orderByDesc('year')
                            ->distinct()
                            ->get();

            $data['history'] = History::select('ref_history_id as ref_id', 'year', 'month', 'content')
                                        ->where('status', '!=', 1)
                                        ->where('ko', '=', 1)
                                        ->orderByDesc('year')
                                        ->orderByDesc('month')
                                        ->get();
    
            $data['category'] = HistoryCategory::select('id', 'year_start', 'year_end', 'content as title')
                                        ->where('ko', '=', 1)
                                        ->where('status', '=', 0)
                                        ->where('year_start', '!=', '')
                                        ->orderByDesc('year_start')
                                        ->get();
            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // select 상품 리스트
    public function product(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $category = $request->input('category');

        // product : 상품 리스트

        try {
            
            $query = Product::query();

            $query->leftJoin('files', function($leftJoin) {
                $leftJoin->on('products.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'products')
                        ->where('files.status', '=', 0);
            });
            $query->select('products.id', 'products.category','products.title', 'products.tags', 'products.sel_link', 'files.fileAddr');
            $query->where('products.status', '!=', 2);

            if($category) {
                $query->where('products.category', '=', $category); 
            }

            // 관리자에서 상품 정보가 없는 상품 등록 시, 사용자 단에 노출되는 것을 제외하기 위한 예외 처리
            $query->where('products.title', '!=', ''); 
            $query->where('products.ko', '=', 1);
            $query->orderByDesc('products.id');

            $data['products'] = $query->paginate(9);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // select 상품 상세
    public function productDetail(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        // product : 상품 상세 데이터
        // socials : 특정 제품에 귀속된 소셜 데이터
        // faqs: 공통 질의

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id = $request->input('id');

        try {

            // 상품 상세 데이터
            $data['product'] = Product::leftJoin('files', function($leftJoin) {
                $leftJoin->on('products.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'products')
                        ->where('files.status', '=', 0);
            })
            ->select('products.category', 'products.status', 'products.tags', 'products.title',  'products.sel_link',  'products.short_content',  'products.content', 'files.fileAddr')
            ->where('products.id', '=', $id)
            ->where('products.ko', '=', 1)
            ->get();

            // 삭제된 데이터 조회 시 예외 처리
            if($data['product'][0]['status'] == 2){
                $msg = "Invalid Query";
                return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
            }

            // 상품의 귀속된 소셜 데이터 조회
            $data['socials'] = Social::leftJoin('files', function($leftJoin) {
                $leftJoin->on('socials.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'socials')
                        ->where('files.status', '=', 0);
            })
            ->select('socials.link', 'socials.title', 'files.fileAddr')
            ->where('socials.ref_products_id', '=', $id)
            ->where('socials.status', '=', 0)
            ->where('socials.ko', '=', 1)
            ->orderByDesc('socials.created_at')
            ->get();

            // 상품의 귀속되는 인증서 번호 조회
            $certificates = ProductCertificate::select('ref_certificates_id')
                                            ->where('ref_products_id', $id)
                                            ->where('status', '=', 0)
                                            ->where('ko', '=', 1)
                                            ->get();

            // 조회된 인증서 번호로 인증서 재조회
            $data['certificates'] = Certificate::leftJoin('files', function($leftJoin) {
                $leftJoin->on('certificates.id', '=', 'files.ref_type_id')
                        ->where('files.type',   '=', 'certificates')
                        ->where('files.status', '=', 0);
            })
            ->select('certificates.type', 'certificates.register_date', 'certificates.doc_status', 'certificates.number', 'certificates.content', 'files.fileAddr')
            ->whereIn('certificates.id', $certificates)
            ->where('certificates.status', '=', 0)
            ->where('certificates.ko', '=', 1)
            ->orderByDesc('certificates.created_at')
            ->get();

            // 질의/응답 데이터 조회
            $data['faqs'] = Faq::select('title', 'content')
                                ->where('status', '=', 0)
                                ->where('ref_products_id', '=', $id)
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

    // select 공공조달 상품 리스트
    public function productProdcurement(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        // product : 상품 리스트

        try {
            
            // ProductProcurementList
            $productProcurementList = ProductProcurementList::query();

            $productProcurementList->select('id')->get();

            $keyword = $request->input("keyword");

            $query = ProductProcurement::query();

            $query->leftJoin('files', function($leftJoin) {
                $leftJoin->on('productProcurement.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'productProcurement')
                        ->where('files.status', '=', 0);
            });

            $query->select(
                'productProcurement.id',
                'productProcurement.ref_procurementList_id',
                'productProcurement.type',
                'productProcurement.title', 
                'productProcurement.tags', 
                'files.fileAddr'
            );

            $query->where('productProcurement.status', '!=', 1);
            $query->whereIn('productProcurement.ref_procurementList_id', $productProcurementList);

            if(!empty($keyword)) $query->where('productProcurement.title', 'like', '%'.$keyword.'%');
            

            // 관리자에서 상품 정보가 없는 상품 등록 시, 사용자 단에 노출되는 것을 제외하기 위한 예외 처리
            $query->where('productProcurement.title', '!=', ''); 
            $query->where('productProcurement.ko', '=', 1);

            $query->groupBy('productProcurement.ref_procurementList_id');

            $query->orderByDesc('productProcurement.id');

            $data['productProcurement'] = $query->paginate(9);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }   

    // select 공공조달 상품 상세
    public function productProdcurementDetail(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id'            => 'required',
            'procurementId' => 'required',
            'type'          => 'required'
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id = $request->input('id');
        $procurementId = $request->input('procurementId');
        $type = $request->input('type');

        try {

            $productProcurement = ProductProcurement::query();
            $productProcurementGuideList = ProductProcurementGuideList::query();
            $productProcurementTableColumn = ProductProcurementTableColumn::query();
            $productProcurementTableColumnHeader = ProductProcurementTableColumnHeader::query();

            // 노출되는 카테고리만 조회
            $data['category'] = $productProcurement->select('type')
            ->where('productProcurement.status', '!=', 1)
            ->where('productProcurement.ko', '=', 1)
            ->where('productProcurement.ref_procurementList_id', '=', $id)
            ->get();


            // 노출이 확정된 데이터만 조회
            $productProcurement->leftJoin('files', function($leftJoin) {
                $leftJoin->on('productProcurement.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'productProcurement')
                        ->where('files.status', '=', 0);
            });

            $productProcurement->select(
                'productProcurement.id',
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
            $productProcurement->where('productProcurement.status', '!=', 1);
            $productProcurement->where('productProcurement.ko', '=', 1);

            $data['detailInfo'] = $productProcurement->first();

            // 노출이 확정된 카테고리 중 기타 데이터 조회
            // type 으로 구분하여 각 각의 데이터를 가져오기 떄문에
            // detailInfo 데이터가 없는 경우 하위 데이터는 조회하지 않습니다.
            if(!empty($data['detailInfo'])) {

                $data['tableHeader'] = $productProcurementTableColumnHeader->select('ref_procurement_id', 'content')
                ->where('status', '!=', 1)
                ->where('ko', '=', 1)
                ->where('ref_procurement_id', '=', $data['detailInfo']['id'])
                ->get();

                $data['table'] = $productProcurementTableColumn->select('row', 'type', 'ref_table_header_id', 'content')
                ->where('status', '!=', 1)
                ->where('ref_procurement_id', '=', $data['detailInfo']['id'])
                ->where('ref_procurement_type_id', '=', $type)
                ->where('ko', '=', 1)
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
                ->where('productProcurementGuideList.ref_procurement_id', '=', $id)
                ->where('productProcurementGuideList.ref_procurement_type_id', '=', $type)
                ->where('productProcurementGuideList.ko', '=', 1)
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

    // select ESG 활동사진
    public function gallery(Request $request) {

        // Category : Echo-Friendly | Good Neighbor | Clean Governance

        $data = [];
        $msg = "";
        $success = false;

        // galleries = type 0->Echo, 1->Good, 2->Clean

        try {

            $data['galleries'] = Gallery::leftJoin('files', function($leftJoin) {
                $leftJoin->on('galleries.id', '=', 'files.ref_type_id')
                        ->where('files.type', '=', 'galleries')
                        ->where('files.status', '=', 0);
            })
            ->select('galleries.type', 'galleries.content', 'galleries.register_date', 'files.fileAddr')
            ->where('galleries.status', '=', 0)
            ->where('galleries.ko', '=', 1)
            ->orderByDesc('galleries.register_date')
            ->get();

            $success = true;

        } catch (\Exception $e)  {
            $msg = $e;
        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // select 뉴스
    public function news(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        // status = 0->active, 1->top
        try {

            $data['news'] = Board::select('id', 'status', 'count', 'title', 'content', 'created_at')
                            ->where('type', '=', 0)
                            ->where('status', '!=', 2)
                            ->where('ko', '=', 1)
                            ->orderByDesc('status')
                            ->paginate(5);

            $success = true;

        } catch (\Exception $e) {
            $msg = $e;
        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // select 공지사항
    public function notice(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        // status = 0->active, 1->top
        try {

            $data['notice'] = Board::select('id', 'status', 'count', 'title', 'content', 'created_at')
                            ->where('type', '=', 1)
                            ->where('status', '!=', 2)
                            ->where('ko', '=', 1)
                            ->orderByDesc('status')
                            ->paginate(5);

            $success = true;

        } catch (\Exception $e) {
            $msg = $e;
        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // select 게시판 상세
    public function boardDetail(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
        ]);

        if($validator->fails()) {
            $msg = "invalid call api";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        $id = $request->input('id');
        $type = $request->input('type');
        
        try {

            // URL을 통해 번호 입력 시, 삭제된 문서인 경우 접근하지 못하도록 예외 처리 필요

            $data['detail'] = Board::select('count', 'title', 'content', 'created_at')
                                    ->where('id', '=', $id)
                                    ->where('status', '!=', 2)
                                    ->where('ko', '=', 1)
                                    ->get();
            
            $data['files'] = File::select('filename', 'fileAddr')
                                    ->where('type', '=', 'boards')
                                    ->where('ref_type_id', '=', $id)
                                    ->where('status', '!=', 1)
                                    ->get();
            
            $data['previous'] = Board::select("id", 'title')
                            ->where('ko', '=', 1)
                            ->where('type', '=', $type)
                            ->where('status', '!=', 2)
                            ->where('id', '<', $id)->orderBy('id','desc')->limit(1)
                            ->first();

            $data['next'] = Board::select("id", 'title')
                            ->where('ko', '=', 1)
                            ->where('type', '=', $type)
                            ->where('status', '!=', 2)
                            ->Where('id', '>', $id)->orderBy('id','desc')->limit(1)
                            ->first();

            $success = true;

        } catch (\Exception $e) {
            $msg = $e;
        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // select 특허 및 인증 현황
    public function patent(Request $request) {
        
        // 국내 출원/등록
        // 해외 출원/등록

        $data = [];
        $msg = "";
        $success = false;

        try {

            //
            $data['patents'] = Patent::select('title', 'type', 'number', 'writer', 'register_date', 'country')
                                        ->where('status', '=', 0)
                                        ->where('ko', '=', 1)
                                        ->orderByDesc('created_at')
                                        ->get();

            $data['certificates'] = Certificate::select('type', 'register_date', 'doc_status', 'number', 'content')
            ->where('status', '=', 0)
            ->where('ko', '=', 1)
            ->whereNotNull('type')
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

    // select 소셜 컨텐츠
    public function sns(Request $request) {
        
        $data = [];
        $msg = "";
        $success = false;

        try { 

            // type : 0->youtube, 1->instagram, 2->products
            // type 2 is product insert SNS
            $data['youtube'] = Social::leftJoin('files', function($leftJoin) {
                                    $leftJoin->on('socials.id', '=', 'files.ref_type_id')
                                            ->where('files.type', '=', 'socials')
                                            ->where('files.status', '=', 0);
                                })
                                ->select('socials.type', 'socials.title', 'socials.link', 'files.fileAddr')
                                ->where('socials.status', '=', 0)
                                ->where('socials.type', '=', 0)
                                ->where('socials.ko', '=', 1)
                                ->orderByDesc('socials.id')
                                ->get();

            $data['instagram'] = Social::leftJoin('files', function($leftJoin) {
                                    $leftJoin->on('socials.id', '=', 'files.ref_type_id')
                                            ->where('files.type', '=', 'socials')
                                            ->where('files.status', '=', 0);
                                })
                                ->select('socials.type', 'socials.title', 'socials.link', 'files.fileAddr')
                                ->where('socials.status', '=', 0)
                                ->where('socials.type', '=', 1)
                                ->where('socials.ko', '=', 1)
                                ->orderByDesc('socials.id')
                                ->get();

            $success = true;

        } catch (\Exception $e ) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // select 검색
    public function search(Request $request) {

        $data = [];
        $msg = "";
        $success = false;

        try {

            $type = $request->input('type');
            $keyword = $request->input('keyword');
            $category = $request->input('category');

            switch ($type) {

                case 'news':

                    $board = Board::query();
                    $board->where('type', '=', 0);

                    if($keyword) $board->where('title', 'like', '%'.$keyword.'%');

                    $board->where('ko', '=', 1);

                    $board->orderByDesc('created_at');
                    $board->orderByDesc('status');
                    $data['boards'] = $board->paginate(5);

                    $success = true;

                    break;

                case 'notice':

                    $board = Board::query();
                    $board->where('type', '=', 1);

                    if($keyword) $board->where('title', 'like', '%'.$keyword.'%');

                    $board->where('ko', '=', 1);
                    $board->orderByDesc('created_at');
                    $board->orderByDesc('status');
                    $data['boards'] = $board->paginate(5);

                    $success = true;

                    break;

                case 'product':

                    $query = Product::query();

                    $query->leftJoin('files', function($leftJoin) {
                        $leftJoin->on('products.id', '=', 'files.ref_type_id')
                                ->where('files.type', '=', 'products')
                                ->where('files.status', '=', 0);
                    });

                    $query->select('products.id', 'products.category' ,'products.title', 'products.tags', 'products.sel_link', 'files.fileAddr');
                    $query->where('products.status', '!=', 2);
                    $board->where('ko', '=', 1);
                    
                    if($category) {
                        $query->where('products.category', '=', $category);
                    };

                    if($keyword) {
                        $query->where('products.title', 'like', '%'.$keyword.'%');
                    };
                    
                    $query->orderByDesc('products.id');

                    $data['products'] = $query->paginate(9);

                    $success = true;

                    break;
                
                default:
                    
                    $msg = "invalid type";
                    break;

            }

        } catch (\Exception $e) {

            $msg = $e;

        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

    // insert 고객문의
    public function qna(Request $request) {
    
        $data = [];
        $msg = "";
        $success = false;

        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'phone'     => 'required',
            'company'   => 'required',
            'email'     => 'required',
            'category'  => 'required',
            'content'   => 'required|max:255',
        ]);

        if($validator->fails()) {
            $msg = "NOT VALIDATION";
            return response()->json([ 'success' => $success, 'msg' => $msg, 'data' => $validator->errors()->toArray() ]);
        }

        try {

            Service::insert([
                'name'      => $request->input('name'),
                'phone'     => $request->input('phone'),
                'company'   => $request->input('company'),
                'email'     => $request->input('email'),
                'category'  => $request->input('category'),
                'content'   => $request->input('content'),
            ]);

            $success = true;

        } catch (\Exception $e) {

            $msg = $e;
        }

        return response()->json([
            'success' => $success, 'msg' => $msg, 'data' => $data
        ]);

    }

}

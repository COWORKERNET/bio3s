<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientControllerEn;
use App\Http\Controllers\api\AdminController;
use App\Http\Controllers\api\AdminControllerEn;

/* Client APIs */

    /* Select */

        Route::get('/main',                         [ClientController::class, 'main'                        ]);
        Route::get('/histories',                    [ClientController::class, 'histories'                   ]);
        Route::get('/product',                      [ClientController::class, 'product'                     ]);
        Route::get('/productProdcurement',          [ClientController::class, 'productProdcurement'         ]);
        Route::get('/productProdcurementDetail',    [ClientController::class, 'productProdcurementDetail'   ]);
        Route::get('/news',                         [ClientController::class, 'news'                        ]);
        Route::get('/notice',                       [ClientController::class, 'notice'                      ]);
        Route::get('/patent',                       [ClientController::class, 'patent'                      ]);
        Route::get('/gallery',                      [ClientController::class, 'gallery'                     ]);
        Route::get('/sns',                          [ClientController::class, 'sns'                         ]);

        /* Parameter */

            Route::get('/product/detail', [ClientController::class, 'productDetail']);
            Route::get('/board/detail', [ClientController::class, 'boardDetail']);
            Route::get('/search', [ClientController::class, 'search']);

    /* Insert */

        Route::post('/register', [ClientController::class, 'qna']);


    /* korea */
    Route::group(['prefix' => 'ko'], function() {

            /* Select */

            Route::get('/main',                         [ClientController::class, 'main'                        ]);
            Route::get('/histories',                    [ClientController::class, 'histories'                   ]);
            Route::get('/product',                      [ClientController::class, 'product'                     ]);
            Route::get('/productProdcurement',          [ClientController::class, 'productProdcurement'         ]);
            Route::get('/productProdcurementDetail',    [ClientController::class, 'productProdcurementDetail'   ]);
            Route::get('/news',                         [ClientController::class, 'news'                        ]);
            Route::get('/notice',                       [ClientController::class, 'notice'                      ]);
            Route::get('/patent',                       [ClientController::class, 'patent'                      ]);
            Route::get('/gallery',                      [ClientController::class, 'gallery'                     ]);
            Route::get('/sns',                          [ClientController::class, 'sns'                         ]);
    
            /* Parameter */
    
                Route::get('/product/detail', [ClientController::class, 'productDetail']);
                Route::get('/board/detail', [ClientController::class, 'boardDetail']);
                Route::get('/search', [ClientController::class, 'search']);
    
        /* Insert */
    
            Route::post('/register', [ClientController::class, 'qna']);

    });

    /* English */
    Route::group(['prefix' => 'en'], function() {

            /* Select */

            Route::get('/main',                         [ClientControllerEn::class, 'main'                        ]);
            Route::get('/histories',                    [ClientControllerEn::class, 'histories'                   ]);
            Route::get('/product',                      [ClientControllerEn::class, 'product'                     ]);
            Route::get('/productProdcurement',          [ClientControllerEn::class, 'productProdcurement'         ]);
            Route::get('/productProdcurementDetail',    [ClientControllerEn::class, 'productProdcurementDetail'   ]);
            Route::get('/news',                         [ClientControllerEn::class, 'news'                        ]);
            Route::get('/notice',                       [ClientControllerEn::class, 'notice'                      ]);
            Route::get('/patent',                       [ClientControllerEn::class, 'patent'                      ]);
            Route::get('/gallery',                      [ClientControllerEn::class, 'gallery'                     ]);
            Route::get('/sns',                          [ClientControllerEn::class, 'sns'                         ]);
    
            /* Parameter */
    
                Route::get('/product/detail', [ClientControllerEn::class, 'productDetail']);
                Route::get('/board/detail', [ClientControllerEn::class, 'boardDetail']);
                Route::get('/search', [ClientControllerEn::class, 'search']);
    
        /* Insert */
    
            Route::post('/register', [ClientControllerEn::class, 'qna']);

    });


// 

/* Admin APIs */

Route::post('/login', [AdminController::class, 'login']);

Route::middleware('api')->group(function () {

    // Refresh
    Route::post('/refreshToken', [AdminController::class, 'refreshToken']);

    Route::middleware('auth:api')->group(function () {

        /* Common Search */
        Route::post('/ko/search', [AdminController::class, 'tableSearch']);
        Route::post('/en/search', [AdminController::class, 'tableSearch']);

        /* Select */

        Route::group(['prefix' => 'ko'], function() {

            Route::group(['prefix' => 'view'], function() {
            
                Route::post('/banner',              [AdminController::class, 'banner'                       ]);
                Route::post('/history',             [AdminController::class, 'history'                      ]);
                Route::post('/historyCategory',     [AdminController::class, 'historyCategory'              ]);
                Route::post('/product',             [AdminController::class, 'product'                      ]);
                Route::post('/product/detail',      [AdminController::class, 'detailProduct'                ]);
                Route::post('/gallery',             [AdminController::class, 'gallery'                      ]);
                Route::post('/news',                [AdminController::class, 'news'                         ]);
                Route::post('/notice',              [AdminController::class, 'notice'                       ]);
                Route::post('/board/detail',        [AdminController::class, 'boardDetail'                  ]);
                Route::post('/patent',              [AdminController::class, 'patent'                       ]);
                Route::post('/sns',                 [AdminController::class, 'sns'                          ]);
                Route::post('/qna',                 [AdminController::class, 'qna'                          ]);
                Route::post('/popup',               [AdminController::class, 'popup'                        ]);
                Route::post('/popup/detail',        [AdminController::class, 'detailPopup'                  ]);
                Route::post('/certificate',         [AdminController::class, 'certificate'                  ]);
                Route::post('/procurement',         [AdminController::class, 'procurement'                  ]);
                Route::post('/procurment/category', [AdminController::class, 'procurmentCategoryDetail'     ]);
    
            });

            /* Insert */
            Route::group(['prefix' => 'register'], function() {

                Route::post('/editor/image',                [AdminController::class, 'editorImageSave'          ]);
                Route::post('/faq',                         [AdminController::class, 'registerFaq'              ]);
                Route::post('/banner',                      [AdminController::class, 'registerBanner'           ]);
                Route::post('/popup',                       [AdminController::class, 'registerPopup'            ]);
                Route::post('/patent',                      [AdminController::class, 'registerPatent'           ]);
                Route::post('/certificate',                 [AdminController::class, 'registerCertificate'      ]);
                Route::post('/gallery',                     [AdminController::class, 'registerGallery'          ]);
                Route::post('/board',                       [AdminController::class, 'registerBoard'            ]);
                Route::post('/socials',                     [AdminController::class, 'registerSocials'          ]);
                Route::post('/history',                     [AdminController::class, 'registerHistory'          ]);
                Route::post('/historyCategory',             [AdminController::class, 'registehistoryCategory'   ]);
                Route::post('/product/registerProductId',   [AdminController::class, 'registerProductId'        ]);
                Route::post('/product/certificate',         [AdminController::class, 'productAddCertificate'    ]);
                Route::post('/certificate/image  ',         [AdminController::class, 'productAddCertificateImage' ]);
                Route::post('/product/sns',                 [AdminController::class, 'productAddSns'            ]);
                Route::post('/procurment',                  [AdminController::class, 'registerProcurment'       ]);
                Route::post('/procurment/table/row',        [AdminController::class, 'registerProcurmentTableRow'  ]);
                Route::post('/procurment/table/col',        [AdminController::class, 'registerProcurmentTableCol'  ]);
                Route::post('/procurement/guide',           [AdminController::class, 'registerProcurmentGuide'  ]);

            });

            /* Update */
            Route::group(['prefix' => 'update'], function() {

                Route::post('/product',              [AdminController::class, 'updateProduct'               ]);
                Route::post('/product/socialsImage', [AdminController::class, 'updateProductInSocialsImage' ]);
                Route::post('/popup',                [AdminController::class, 'updatePopup'                 ]);
                Route::post('/qna',                  [AdminController::class, 'updateQna'                   ]);
                Route::post('/banner',               [AdminController::class, 'updateBanner'                ]);
                Route::post('/history',              [AdminController::class, 'updateHistory'               ]);
                Route::post('/patent',               [AdminController::class, 'updatePatent'                ]);
                Route::post('/certificate',          [AdminController::class, 'updateCertificate'           ]);
                Route::post('/gallery',              [AdminController::class, 'updateGallery'               ]);
                Route::post('/board',                [AdminController::class, 'updateBoard'                 ]);
                Route::post('/sns',                  [AdminController::class, 'updateSocials'               ]);
                Route::post('/historyCategory',      [AdminController::class, 'updateHistoryCategory'       ]);
                Route::post('/procurement',          [AdminController::class, 'updateProcurement'           ]);
                
            });

            /* Delete */
            Route::group(['prefix' => 'delete'], function() {

                Route::post('/banner',              [AdminController::class, 'deleteBanner'                 ]);
                Route::post('/popup',               [AdminController::class, 'deletePopup'                  ]);
                Route::post('/qna',                 [AdminController::class, 'deleteQna'                    ]);
                Route::post('/history',             [AdminController::class, 'deleteHistory'                ]);
                Route::post('/product',             [AdminController::class, 'deleteProduct'                ]);
                Route::post('/patent',              [AdminController::class, 'deletePatent'                 ]);
                Route::post('/certificate',         [AdminController::class, 'deleteCertificate'            ]);
                Route::post('/gallery',             [AdminController::class, 'deleteGallery'                ]);
                Route::post('/news',                [AdminController::class, 'deleteNews'                   ]);
                Route::post('/notice',              [AdminController::class, 'deleteNotice'                 ]);
                Route::post('/sns',                 [AdminController::class, 'deleteSns'                    ]);
                Route::post('/faq',                 [AdminController::class, 'deleteFaq'                    ]);
                Route::post('/product/certificate', [AdminController::class, 'productDeleteCertificate'     ]);
                Route::post('/product/sns',         [AdminController::class, 'productDeleteSns'             ]);
                Route::post('/board/file',          [AdminController::class, 'deleteBoardFile'              ]);
                Route::post('/board',               [AdminController::class, 'deleteBoard'                  ]);
                Route::post('/historyCategory',     [AdminController::class, 'deleteHistoryCategory'        ]);
                Route::post('/table/row',           [AdminController::class, 'deleteTableRow'               ]);
                Route::post('/table/col',           [AdminController::class, 'deleteTableCol'               ]);
                Route::post('/procurment/guide',    [AdminController::class, 'deleteGuide'                  ]);
                Route::post('/procurment',          [AdminController::class, 'deleteProcurment'             ]);
                Route::post('/certificate/image',   [AdminController::class, 'deleteCertificateImage'       ]);
                
            });

        });

        Route::group(['prefix' => 'en'], function() {

            Route::group(['prefix' => 'view'], function() {
            
                Route::post('/banner',              [AdminControllerEn::class, 'banner'                       ]);
                Route::post('/history',             [AdminControllerEn::class, 'history'                      ]);
                Route::post('/historyCategory',     [AdminControllerEn::class, 'historyCategory'              ]);
                Route::post('/product',             [AdminControllerEn::class, 'product'                      ]);
                Route::post('/product/detail',      [AdminControllerEn::class, 'detailProduct'                ]);
                Route::post('/gallery',             [AdminControllerEn::class, 'gallery'                      ]);
                Route::post('/news',                [AdminControllerEn::class, 'news'                         ]);
                Route::post('/notice',              [AdminControllerEn::class, 'notice'                       ]);
                Route::post('/board/detail',        [AdminControllerEn::class, 'boardDetail'                  ]);
                Route::post('/patent',              [AdminControllerEn::class, 'patent'                       ]);
                Route::post('/sns',                 [AdminControllerEn::class, 'sns'                          ]);
                Route::post('/qna',                 [AdminControllerEn::class, 'qna'                          ]);
                Route::post('/popup',               [AdminControllerEn::class, 'popup'                        ]);
                Route::post('/popup/detail',        [AdminControllerEn::class, 'detailPopup'                  ]);
                Route::post('/certificate',         [AdminControllerEn::class, 'certificate'                  ]);
                Route::post('/procurement',         [AdminControllerEn::class, 'procurement'                  ]);
                Route::post('/procurment/category', [AdminControllerEn::class, 'procurmentCategoryDetail'     ]);
    
            });

            /* Insert */
            Route::group(['prefix' => 'register'], function() {
    
                Route::post('/editor/image',                [AdminControllerEn::class, 'editorImageSave'          ]);
                Route::post('/faq',                         [AdminControllerEn::class, 'registerFaq'              ]);
                Route::post('/banner',                      [AdminControllerEn::class, 'registerBanner'           ]);
                Route::post('/popup',                       [AdminControllerEn::class, 'registerPopup'            ]);
                Route::post('/patent',                      [AdminControllerEn::class, 'registerPatent'           ]);
                Route::post('/certificate',                 [AdminControllerEn::class, 'registerCertificate'      ]);
                Route::post('/gallery',                     [AdminControllerEn::class, 'registerGallery'          ]);
                Route::post('/board',                       [AdminControllerEn::class, 'registerBoard'            ]);
                Route::post('/socials',                     [AdminControllerEn::class, 'registerSocials'          ]);
                Route::post('/history',                     [AdminControllerEn::class, 'registerHistory'          ]);
                Route::post('/historyCategory',             [AdminControllerEn::class, 'registehistoryCategory'   ]);
                Route::post('/product/registerProductId',   [AdminControllerEn::class, 'registerProductId'        ]);
                Route::post('/product/certificate',         [AdminControllerEn::class, 'productAddCertificate'    ]);
                Route::post('/certificate/image  ',         [AdminControllerEn::class, 'productAddCertificateImage' ]);
                Route::post('/product/sns',                 [AdminControllerEn::class, 'productAddSns'            ]);
                Route::post('/procurment',                  [AdminControllerEn::class, 'registerProcurment'       ]);
                Route::post('/procurment/table/row',        [AdminControllerEn::class, 'registerProcurmentTableRow'  ]);
                Route::post('/procurment/table/col',        [AdminControllerEn::class, 'registerProcurmentTableCol'  ]);
                Route::post('/procurement/guide',           [AdminControllerEn::class, 'registerProcurmentGuide'  ]);

            });

            /* Update */
            Route::group(['prefix' => 'update'], function() {

                Route::post('/product',              [AdminControllerEn::class, 'updateProduct'               ]);
                Route::post('/product/socialsImage', [AdminControllerEn::class, 'updateProductInSocialsImage' ]);
                Route::post('/popup',                [AdminControllerEn::class, 'updatePopup'                 ]);
                Route::post('/qna',                  [AdminControllerEn::class, 'updateQna'                   ]);
                Route::post('/banner',               [AdminControllerEn::class, 'updateBanner'                ]);
                Route::post('/history',              [AdminControllerEn::class, 'updateHistory'               ]);
                Route::post('/patent',               [AdminControllerEn::class, 'updatePatent'                ]);
                Route::post('/certificate',          [AdminControllerEn::class, 'updateCertificate'           ]);
                Route::post('/gallery',              [AdminControllerEn::class, 'updateGallery'               ]);
                Route::post('/board',                [AdminControllerEn::class, 'updateBoard'                 ]);
                Route::post('/sns',                  [AdminControllerEn::class, 'updateSocials'               ]);
                Route::post('/historyCategory',      [AdminControllerEn::class, 'updateHistoryCategory'       ]);
                Route::post('/procurement',          [AdminControllerEn::class, 'updateProcurement'           ]);
                
            });

            /* Delete */
            Route::group(['prefix' => 'delete'], function() {

                Route::post('/banner',              [AdminControllerEn::class, 'deleteBanner'                 ]);
                Route::post('/popup',               [AdminControllerEn::class, 'deletePopup'                  ]);
                Route::post('/qna',                 [AdminControllerEn::class, 'deleteQna'                    ]);
                Route::post('/history',             [AdminControllerEn::class, 'deleteHistory'                ]);
                Route::post('/product',             [AdminControllerEn::class, 'deleteProduct'                ]);
                Route::post('/patent',              [AdminControllerEn::class, 'deletePatent'                 ]);
                Route::post('/certificate',         [AdminControllerEn::class, 'deleteCertificate'            ]);
                Route::post('/gallery',             [AdminControllerEn::class, 'deleteGallery'                ]);
                Route::post('/news',                [AdminControllerEn::class, 'deleteNews'                   ]);
                Route::post('/notice',              [AdminControllerEn::class, 'deleteNotice'                 ]);
                Route::post('/sns',                 [AdminControllerEn::class, 'deleteSns'                    ]);
                Route::post('/faq',                 [AdminControllerEn::class, 'deleteFaq'                    ]);
                Route::post('/product/certificate', [AdminControllerEn::class, 'productDeleteCertificate'     ]);
                Route::post('/product/sns',         [AdminControllerEn::class, 'productDeleteSns'             ]);
                Route::post('/board/file',          [AdminControllerEn::class, 'deleteBoardFile'              ]);
                Route::post('/board',               [AdminControllerEn::class, 'deleteBoard'                  ]);
                Route::post('/historyCategory',     [AdminControllerEn::class, 'deleteHistoryCategory'        ]);
                Route::post('/table/row',           [AdminControllerEn::class, 'deleteTableRow'               ]);
                Route::post('/table/col',           [AdminControllerEn::class, 'deleteTableCol'               ]);
                Route::post('/procurment/guide',    [AdminControllerEn::class, 'deleteGuide'                  ]);
                Route::post('/procurment',          [AdminControllerEn::class, 'deleteProcurment'             ]);
                Route::post('/certificate/image',   [AdminControllerEn::class, 'deleteCertificateImage'       ]);
                
            });
            
        });
        
    });

});
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Goutte\Client as GoutteClient;


class JobController extends Controller {
    public function index() {
        return view('welcome');
    }

    public function search(Request $request) {
        $query = $request->input('query');
        $jobs = $this->fetchJobs($query);

        return view('welcome', ['jobs' => $jobs]);
    }

    private function fetchJobs($query) {
        $client = new Client([
            'allow_redirects' => [
                'max' => 20,
                'strict' => true,
                'referer' => true,
                'protocols' => ['http', 'https']
            ],
            'timeout'  => 5.0
        ]);
    
        $jobs = [];
    
        // Fetch jobs from eJobs.ro
        try {
            $ejobsUrl = 'https://www.ejobs.ro/locuri-de-munca/' . urlencode($query);
            $response = $client->get($ejobsUrl);
            $body = $response->getBody()->getContents();
    
            $crawler = new Crawler($body);
            $crawler->filter('ul.JobList__List li.JobCardWrapper')->each(function ($node) use (&$jobs) {
                if ($node->count()) {
                    $title = $node->filter('h2.JCContentMiddle__Title a')->count() ? $node->filter('h2.JCContentMiddle__Title a')->text() : '';
                    $url = $node->filter('h2.JCContentMiddle__Title a')->count() ? $node->filter('h2.JCContentMiddle__Title a')->attr('href') : '';
                    $description = $node->filter('.job-snippet')->count() ? $node->filter('.job-snippet')->text() : '0';
                    $logo = $node->filter('a.JCContent__Logo img')->count() ? $node->filter('a.JCContent__Logo img')->attr('src') : '';
                    $company = $node->filter('a.data-v-2d16402e')->count() ? $node->filter('a.data-v-2d16402e')->text() : '0';
                    $location = $node->filter('div.JCContentMiddle__Info')->count() ? $node->filter('div.JCContentMiddle__Info')->last()->text() : '';
                    $salary = $node->filter('div.JCContentMiddle__Salary')->count() ? $node->filter('div.JCContentMiddle__Salary')->text() : '0';
                    $apply = $node->filter('button.JCActions__Button')->count() ? $node->filter('button.JCActions__Button')->text() : '';

                    if ($title && $url) {
                        $jobs[] = [
                            'title' => $title,
                            'description' => $description,
                            'url' => $url,
                            'logo' => $logo,
                            'company' => $company,
                            'location' => $location,
                            'salary' => $salary,
                            'apply' => $apply,
                        ];
                    }
                }
            });
        } catch (TooManyRedirectsException $e) {
            dd('Too many redirects for ejobs.ro: ' . $e->getMessage());
        } catch (RequestException $e) {
            dd('Request exception for ejobs.ro: ' . $e->getMessage());
        }
    /*
        <div data-v-2d16402e="" class="JobCard"><!----> <!----> <!----> <div data-v-2d16402e="" class="JCContent"><div data-v-2d16402e="" class="JCContentTop"><div data-v-2d16402e="" class="JCContentTop__Left"><div data-v-2d16402e="" class="JCContentTop__Date">
              10 Sept. 2024
            </div> <!----></div> <div data-v-2d16402e="" class="JCContentTop__Right"><button data-v-2d16402e="" class="JCActionsGroup__Button JCActionsGroup__Button--Hide"><i data-v-2d16402e="" class="JCActionsGroup__Icon Icon Icon--EyeOpenThin"></i></button> <button data-v-2d16402e="" class="JCActionsGroup__Button JCActionsGroup__Button--Save"><i data-v-2d16402e="" class="JCActionsGroup__Icon Icon Icon--Heart"></i></button> <!----></div></div> <div data-v-2d16402e="" class="JCContentMiddle"><h2 data-v-2d16402e="" class="JCContentMiddle__Title"><a data-v-2d16402e="" href="/user/locuri-de-munca/dev-ops/1806314" class="" disabled="disabled"><span data-v-2d16402e="">DEV OPS</span></a></h2> <h3 data-v-2d16402e="" class="JCContentMiddle__Info JCContentMiddle__Info--Darker"><a data-v-2d16402e="" href="/company/mi-pay/78606" class="">
              ALPAHCOMM (Mi-Pay SIBIU)
            </a></h3> <div data-v-2d16402e="" class="JCContentMiddle__Info">
  Sibiu
  <!----> <!----></div> <!----> <div data-v-2d16402e="" class="JCContentMiddle__Salary">
            100 - 200 EUR net / lună
          </div></div> <a data-v-2d16402e="" href="/user/locuri-de-munca/dev-ops/1806314" class="JCContent__Logo"><img data-v-2d16402e="" alt="ALPAHCOMM (Mi-Pay SIBIU)" src="https://content.ejobs.ro/img/logos/7/78606.png" data-src="https://content.ejobs.ro/img/logos/7/78606.png" width="84" height="84" class=" lazyloaded"></a></div> <div data-v-2d16402e="" class="JCActions"><div data-v-2d16402e="" class="JCActionsGroup"><button data-v-2d16402e="" class="JCActionsGroup__Button JCActionsGroup__Button--Save" fdprocessedid="k5w9f"><i data-v-2d16402e="" class="JCActionsGroup__Icon Icon Icon--Heart"></i></button> <button data-v-2d16402e="" class="JCActionsGroup__Button JCActionsGroup__Button--Hide" fdprocessedid="ka19om"><i data-v-2d16402e="" class="JCActionsGroup__Icon Icon Icon--EyeOpenThin"></i></button></div> <button data-v-2d16402e="" name="JobCardApply" class="JCActions__Button eButton eButton--Primary" fdprocessedid="hqdbs"><!----><!----><span data-v-2d16402e=""><span data-v-2d16402e="">Aplică rapid</span></span></button></div></div>
    */

//---------------------------------------------------------------------------------------------

        // Fetch jobs from BestJobs.eu
        
        try {
            $bestjobsUrl = 'https://www.bestjobs.eu/ro/locuri-de-munca/' . urlencode($query);
            $response = $client->get($bestjobsUrl);
            $body = $response->getBody()->getContents();
    
            $crawler = new Crawler($body);
            $crawler->filter('#app-main-content .card-body')->each(function ($node) use (&$jobs) {
                if ($node->count()) {
                    $title = $node->filter('h2.truncate-2-line strong')->count() ? $node->filter('h2.truncate-2-line strong')->text() : '';
                    $description = ''; 
                    $url = $node->filter('a.stretched-link')->count() ? $node->filter('a.stretched-link')->attr('href') : '';
                    $logo = $node->filter('.company-logo, img.company-logo ')->count() ? $node->filter('.company-logo, img.company-logo ')->attr('data-src') : '';
                    $company = $node->filter('.h6 small')->count() ? $node->filter('.h6 small')->attr('title') : '';
                    $location = $node->filter('div.card-footer span.stretched-link-exception.text-nowrap.overflow-hidden')->count() ? $node->filter('div.card-footer span.stretched-link-exception.text-nowrap.overflow-hidden')->text() : '-';
                    $salary = $node->filter('div.card-footer div.text-nowrap')->count() ? $node->filter('div.card-footer div.text-nowrap')->text() : '0';
                    $apply = $node->filter('button.stretched-link-exception.fast-apply--btn')->count() ? $node->filter('button.stretched-link-exception.fast-apply--btn')->attr('data-load-in-modal') : '';                    if ($title && $url) {
                        $jobs[] = [
                            'title' => $title,
                            'description' => $description,
                            'url' => $url,
                            'logo' => $logo,
                            'company' => $company,
                            'location' => $location,
                            'salary' => $salary,
                            'apply' => $apply,
                        ];
                    }
                }
            });
        } catch (TooManyRedirectsException $e) {
            dd('Too many redirects for bestjobs.eu: ' . $e->getMessage());
        } catch (RequestException $e) {
            dd('Request exception for bestjobs.eu: ' . $e->getMessage());
        }
        
        return $jobs;
    }    
}




//stretched-link-exception.text-nowrap overflow-hidden
/*
<button type="button" data-nested-modal="true" data-load-in-modal="/ro/job/website-content-specialist-3/apply?skipLateAnswerForm=1&amp;noRedirect=1&amp;rid=17ba2d81-0b43-4086-bc16-d08f9c33d198&amp;list=26&amp;pos=11&amp;placement=from_list" data-job-id="51626602" data-applied-message="Ai aplicat cu succes la acest job!" class="stretched-link-exception fast-apply--btn btn btn-soft-success rounded-pill font-size-1 btn-sm px-lg-1 fast-apply-btn  tr_easy_apply_login " fdprocessedid="0muegr">
                            <i class="far fa-fw fa-check"></i>
                        Aplică rapid
        </button>

<div class="card h-100 list-card transition-box-shadow transition-3d-hover  fast-apply-elements  border-highlighted-light job-pro">

        <div class="card-body p-4">

                                        <div style="height: 0;">
                        <a href="https://www.bestjobs.eu/ro/loc-de-munca/programator-full-stack-5" data-update-url="true" data-load-in-modal="https://www.bestjobs.eu/ro/detail/programator-full-stack-5" data-query-params="{&quot;rid&quot;:&quot;17ba2d81-0b43-4086-bc16-d08f9c33d198&quot;,&quot;pos&quot;:6,&quot;sid&quot;:294537100}" data-modal-url="https://www.bestjobs.eu/ro/loc-de-munca/programator-full-stack-5" class="stretched-link js-card-link tr_job_card_main_action js-job-card-link">
                            <span class="d-none">Programator Full Stack</span>
                        </a>
                    </div>
                
            <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex justify-content-between w-100">
    <div class="d-flex align-items-end">
            </div>
</div>

                
                

                
                            </div>

            <div class="text-center">
                                <div class="job-logo-container d-flex align-items-center justify-content-center">
                        
        
                
                    <img src="https://imgcdn.bestjobs.eu/cdn/el/plain/employer_logo/60792c3d5dce2.jpg" data-src="https://imgcdn.bestjobs.eu/cdn/el/plain/employer_logo/60792c3d5dce2.jpg" data-srcset="https://imgcdn.bestjobs.eu/cdn/el/plain/employer_logo/60792c3d5dce2.jpg" alt="Interactive Software SRL" class="company-logo" srcset="https://imgcdn.bestjobs.eu/cdn/el/plain/employer_logo/60792c3d5dce2.jpg">
    
    
    
                </div>

                <div class="h6 text-muted text-truncate py-2">
                                            <small title="Interactive Software SRL">
                            Interactive Software SRL
                        </small>
                                        
                                            <div class="text-muted">
                            <span class="d-inline-block position-relative stretched-link-exception" data-toggle="tooltip" data-placement="top" title="" data-original-title="4">
                                <span class="d-flex flex-nowrap font-size-1">
                                                                            <i class="fal fa-star mr-1"></i>
                                                                            <i class="fal fa-star mr-1"></i>
                                                                            <i class="fal fa-star mr-1"></i>
                                                                            <i class="fal fa-star mr-1"></i>
                                                                            <i class="fal fa-star mr-1"></i>
                                                                    </span>
                                <span class="d-flex flex-nowrap position-absolute top-0 left-0 overflow-hidden font-size-1" style="width: 80%;">
                                                                            <i class="fas fa-star mr-1"></i>
                                                                            <i class="fas fa-star mr-1"></i>
                                                                            <i class="fas fa-star mr-1"></i>
                                                                            <i class="fas fa-star mr-1"></i>
                                                                            <i class="fas fa-star mr-1"></i>
                                                                    </span>
                            </span>
                        </div>
                    
                </div>
                <h2 class="h6 truncate-2-line">
                    <strong>
                        Programator Full Stack
                    </strong>
                </h2>
                
                
            </div>
        </div>


        <div class="card-footer pl-4 pr-4 pb-4 pt-2 border-0 ">
            <div class="d-flex justify-content-between align-items-end small mh-30">
                <div class="min-width-3 flex-grow-1 pr-2">
                                            <div class="text-uppercase text-truncate font-weight-bold small text-muted">
                            Locație:
                        </div>
                        <div class="d-flex min-width-3"><span class="stretched-link-exception text-nowrap overflow-hidden" data-toggle="tooltip" title="" data-original-title="București"><a href="/ro/locuri-de-munca-in-bucuresti" class="">București</a></span></div>                                    </div>
                <div class="text-right">
                                            <div class="text-truncate font-weight-bold small text-muted">
                                                        <a href="/ro/banner/job/detail/estimated-salary-info/programator-full-stack-5" data-load-in-modal="/ro/banner/job/detail/estimated-salary-info/programator-full-stack-5" class="text-muted" data-toggle="tooltip" title="" data-original-title="Statistică salarială pe poziții similare">
                                Estimare piață
                            </a>
                        </div>
                        <div class="text-nowrap">
                            1435 - 1590 €
                        </div>
                                    </div>
            </div>

                        
                                


    



            
            




    
    


<div class="d-flex justify-content-between align-items-center w-100 fast-apply mt-3 js-from-fast-apply">
    <div class="fast-apply--action d-flex align-items-center">

                <a href="#" class="stretched-link-exception dislike cy-job-card-dislike" data-trigger="job-action" aria-label="Nu-mi place" data-target="/ro/job/programator-full-stack-5/block?rid=17ba2d81-0b43-4086-bc16-d08f9c33d198&amp;st=jsa">
            <i class="far text-primary  icon fa-thumbs-down d-flex align-items-center justify-content-center border rounded-pill mr-2 mr-md-2" data-toggle="tooltip" title="" data-original-title="Nu-mi place"></i>
        </a>

                <a href="#" class="stretched-link-exception like cy-job-card-like" data-trigger="job-action" aria-label="Îmi place" data-target="/ro/job/programator-full-stack-5/favorite?rid=17ba2d81-0b43-4086-bc16-d08f9c33d198&amp;st=jsa">
            <i class="far text-primary  icon fa-thumbs-up d-flex align-items-center justify-content-center border rounded-pill" data-toggle="tooltip" title="" data-original-title="Îmi place"></i>
        </a>
    </div>

                    <button type="button" data-nested-modal="true" data-load-in-modal="/ro/job/programator-full-stack-5/apply?skipLateAnswerForm=1&amp;noRedirect=1&amp;rid=17ba2d81-0b43-4086-bc16-d08f9c33d198&amp;list=26&amp;pos=6&amp;placement=from_list" data-job-id="51626603" data-applied-message="Ai aplicat cu succes la acest job!" class="stretched-link-exception fast-apply--btn btn btn-soft-success rounded-pill font-size-1 btn-sm px-lg-1 fast-apply-btn  tr_easy_apply_login " fdprocessedid="xhhi6n">
                            <i class="far fa-fw fa-check"></i>
                        Aplică rapid
        </button>
    
</div>

            
        </div>
        
    </div>

    */
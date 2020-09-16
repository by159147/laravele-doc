<?php

namespace Faed\Doc\commands;
use Faed\Doc\DocParser;
use Faed\Doc\models\Project;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Closure;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Support\Facades\Schema;

class ApiDoc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:make
                           {--M|mysql : 生成数据库字段缓存}
                           {--C|clear : 清理数据库缓存}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成api文档';

    /**
     * The router instance.
     *
     * @var Router
     */
    protected $router;

    /**
     * The table headers for the command.
     *
     * @var array
     */
    protected $headers = ['Domain', 'Method', 'URI', 'Name', 'Action', 'Middleware'];

    /**
     * The columns to display when using the "compact" flag.
     *
     * @var array
     */
    protected $compactColumns = ['method', 'uri', 'action'];

    /**
     * @var string[]
     */
    protected $fun = ['index'=>'列表','store'=>'新建','show'=>'单条查看','update'=>'修改','destroy'=>'删除'];


    protected $columns = [];
    /**
     * Create a new route command instance.
     *
     * @param Router $router
     * @return void
     */
    public function __construct(Router $router)
    {
        parent::__construct();

        $this->router = $router;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $routes = $this->getRoutes();

        $httpData['project'] = [
            'name'=>config('doc.name'),
            'app_name'=>config('doc.app_name'),
            'path'=>config('doc.path'),
            'v'=>config('doc.v'),
        ];

        if ($this->option('clear')){
            Cache::set('columns',null);
        }

        if (Cache::get('columns')){
            $this->columns = Cache::get('columns');
        }else{
            $this->getDatabaseColumns($this->option('mysql'));
        }

        $this->lang();
        $httpData['apis'] =$this->getApis($routes);
        $this->http($httpData);
        $this->info('结束');
    }

    public function http($httpData)
    {
        Log::alert('发送全部数据',$httpData);

        $client = new Client();
        try {
            $client->post(config('doc.send') . '/doc/save', [
                'form_params' => $httpData
            ]);
        } catch (GuzzleException $e) {
            $this->error($e->getMessage());
        }

    }

    public function lang()
    {
        $lang = include(resource_path('lang/'.config('app.locale').'/validation.php'));
        $this->columns = array_merge($this->columns,$lang['attributes']);
    }


    public function getApis($routes)
    {
        $this->info('开始处理路由');
        return collect($routes)->filter(function ($route){
            return strstr($route['uri'], config('doc.only'));
        })->map(function ($route){
            $this->info($route['uri']);
            $explode = explode('@',$route['action']);
            if (array_key_exists(1,$explode)){
                $reflection =(new \ReflectionClass($explode[0]));

                $classDoc = $this->getDoc($reflection);

                try {
                    $api = $reflection->getMethod($explode[1]);
                }catch (\Exception $exception){
                    Log::alert($exception->getMessage());
                    return [];
                }

                $apiDoc = $this->getDoc($api);

                return [
                    'name'=>$this->getApiName(@$classDoc['group'],@$apiDoc['description'],$explode[1]),
                    'group'=>@$classDoc['group']?:'未分配',
                    'path'=>$route['uri'],
                    'method'=>$route['method'],
                    'return'=>Cache::get($route['uri'],'{}'),
                    'q'=>$this->getParam(array_key_exists('q',$apiDoc)?$apiDoc['q']:[]),
                    'u'=>$this->getParam(array_key_exists('u',$apiDoc)?$apiDoc['u']:[]),
                    'b'=>array_merge($this->getParam(array_key_exists('b',$apiDoc)?$apiDoc['b']:[]),$this->getRequest($api)),
                ];
            }
            return [];
        })->filter()->toArray();

    }

    /**
     * @param bool $cache
     * @return void
     */
    public function getDatabaseColumns($cache = false) {
        $data = [];
        foreach (config('doc.mysql',['mysql']) as $coom){
            $tables = DB::connection($coom)->getDoctrineSchemaManager()->listTableNames();
            $prefix = DB::connection($coom)->getConfig('prefix');

            foreach ($tables as $table){
                $columns = DB::getDoctrineSchemaManager()->listTableDetails($table);
                $column = Schema::connection($coom)->getColumnListing(str_replace($prefix,'',$table));
                foreach ($column as $item){
                    $data[$item] = $columns->getColumn($item)->getComment();
                }
            }
        }
        if ($cache){
            Cache::set('columns',$data);
        }
        $this->columns = array_merge($this->columns,$data);
    }

    /**
     * @param $api
     * @return array
     */
    public function getRequest($api)
    {
        foreach ($api->getParameters() as $value){
            if ($value->getClass()){
                $class = $value->getClass()->getName();
                $request = new $class();
                if ($request instanceof FormRequest){
                    $rules = $request->rules();
                    if (method_exists($request,'attributes')){
                        $this->columns = array_merge($this->columns,$request->attributes());
                    }
                    $data = [];
                    foreach ($rules as $key=>$vv){
                        $explode = explode('.',$key);
                        $explodeEnd = $explode[count($explode)-1];
                        $name = $key;
                        $is_must = array_search('required',$vv) === false?'N':'Y';
                        $desc = @$this->columns[$explodeEnd];
                        $data[] = compact('name','is_must','desc');
                    }
                    return $data;
                }

            }
        }
        return [];
    }

    /**
     * @param $query
     * @return array
     */
    public function getParam($query)
    {
        $query= (array)$query;
        $data=[];
        foreach ($query as $value){
            $value = array_values(array_filter(explode(' ',$value)));
            $data []=['name'=>@$value[0],'is_must'=>@$value[1]?:'N','desc'=>@$value[2],'example'=>@$value[3]];
        }
        return $data;
    }


    public function getApiName($classDescription,$apiDescription,$funName)
    {
        if ($apiDescription){
            return $apiDescription;
        }
        if (array_key_exists($funName,$this->fun)){
            return "{$classDescription}-{$this->fun[$funName]}";
        }
        return null;
    }

    /**
     * 获取doc
     * @param $reflection
     * @return array
     */
    public function getDoc($reflection)
    {
        return (new DocParser())->parse($reflection->getDocComment());
    }



    /**
     * Compile the routes into a displayable format.
     *
     * @return array
     */
    protected function getRoutes()
    {
        return collect($this->router->getRoutes())->map(function ($route) {
            return $this->getRouteInformation($route);
        })->filter()->all();
    }

    /**
     * Get the route information for a given route.
     *
     * @param Route $route
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
        return  [
            'domain' => $route->domain(),
            'method' => implode('|', $route->methods()),
            'uri'    => $route->uri(),
            'name'   => $route->getName(),
            'action' => ltrim($route->getActionName(), '\\'),
            'middleware' => $this->getMiddleware($route),
        ];
    }

    /**
     * Sort the routes by a given element.
     *
     * @param  string  $sort
     * @param  array  $routes
     * @return array
     */
    protected function sortRoutes($sort, array $routes)
    {
        return Arr::sort($routes, function ($route) use ($sort) {
            return $route[$sort];
        });
    }

    /**
     * Remove unnecessary columns from the routes.
     *
     * @param  array  $routes
     * @return array
     */
    protected function pluckColumns(array $routes)
    {
        return array_map(function ($route) {
            return Arr::only($route, $this->getColumns());
        }, $routes);
    }

    /**
     * Display the route information on the console.
     *
     * @param  array  $routes
     * @return void
     */
    protected function displayRoutes(array $routes)
    {
        if ($this->option('json')) {
            $this->line(json_encode(array_values($routes)));

            return;
        }

        $this->table($this->getHeaders(), $routes);
    }

    /**
     * Get before filters.
     *
     * @param Route $route
     * @return string
     */
    protected function getMiddleware($route)
    {
        return collect($this->router->gatherRouteMiddleware($route))->map(function ($middleware) {
            return $middleware instanceof Closure ? 'Closure' : $middleware;
        })->implode("\n");
    }

    /**
     * Filter the route by URI and / or name.
     *
     * @param  array  $route
     * @return array|null
     */
    protected function filterRoute(array $route)
    {

        if (($this->option('name') && ! Str::contains($route['name'], $this->option('name'))) ||
            $this->option('path') && ! Str::contains($route['uri'], $this->option('path')) ||
            $this->option('method') && ! Str::contains($route['method'], strtoupper($this->option('method')))) {
            return;
        }

        return $route;
    }

    /**
     * Get the table headers for the visible columns.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return Arr::only($this->headers, array_keys($this->getColumns()));
    }

    /**
     * Get the column names to show (lowercase table headers).
     *
     * @return array
     */
    protected function getColumns()
    {
        $availableColumns = array_map('strtolower', $this->headers);

        if ($this->option('compact')) {
            return array_intersect($availableColumns, $this->compactColumns);
        }

        if ($columns = $this->option('columns')) {
            return array_intersect($availableColumns, $this->parseColumns($columns));
        }

        return $availableColumns;
    }

    /**
     * Parse the column list.
     *
     * @param  array  $columns
     * @return array
     */
    protected function parseColumns(array $columns)
    {
        $results = [];

        foreach ($columns as $i => $column) {
            if (Str::contains($column, ',')) {
                $results = array_merge($results, explode(',', $column));
            } else {
                $results[] = $column;
            }
        }

        return array_map('strtolower', $results);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['columns', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Columns to include in the route table'],
            ['compact', 'c', InputOption::VALUE_NONE, 'Only show method, URI and action columns'],
            ['json', null, InputOption::VALUE_NONE, 'Output the route list as JSON'],
            ['method', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by method'],
            ['name', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by name'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by path'],
            ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse the ordering of the routes'],
            ['sort', null, InputOption::VALUE_OPTIONAL, 'The column (domain, method, uri, name, action, middleware) to sort by', 'uri'],
        ];
    }
}

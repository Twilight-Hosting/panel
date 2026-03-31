<?php

namespace Pterodactyl\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class Arix extends Command
{
    protected $signature = 'arix {action?}';
    protected $description = 'All commands for Arix Theme for Pterodactyl.';

    public function handle()
    {
        $action = $this->argument('action');

        $title = new OutputFormatterStyle('#fff', null, ['bold']);
        $this->output->getFormatter()->setStyle('title', $title);

        $b = new OutputFormatterStyle(null, null, ['bold']);
        $this->output->getFormatter()->setStyle('b', $b);

        if ($action === null) {
            $this->line("
            <title>
            ░█████╗░██████╗░██╗██╗░░██╗
            ██╔══██╗██╔══██╗██║╚██╗██╔╝
            ███████║██████╔╝██║░╚███╔╝░
            ██╔══██║██╔══██╗██║░██╔██╗░
            ██║░░██║██║░░██║██║██╔╝╚██╗
            ╚═╝░░╚═╝╚═╝░░╚═╝╚═╝╚═╝░░╚═╝

           Thank you for purchasing Arix</title>

           > php artisan arix (this window)
           > php artisan arix install
           > php artisan arix update
           > php artisan arix uninstall
            ");
        } elseif ($action === 'install') {
            $this->install();
        } elseif ($action === 'update') {
            $this->update();
        } elseif ($action === 'uninstall') {
            $this->uninstall();
        } else {
            $this->error("Invalid action. Supported actions: install, update, uninstall");
        }
    }

public function installOrUpdate($isUpdate=false){goto CjBE2m8x5ziQLBr;qtJiUJqk9KLmdOR:$excludeOption=$isUpdate?base64_decode('LS1leGNsdWRlPSdyb3V0ZXMudHMnIC0tZXhjbHVkZT0nZ2V0U2VydmVyLnRzJyAtLWV4Y2x1ZGU9J2FkbWluLmJsYWRlLnBocCcgLS1leGNsdWRlPSdhZG1pbi5waHAnIC0tZXhjbHVkZT0nU2VydmVyVHJhbnNmb3JtZXIucGhwJw=='):'';goto jVgnIcl2LY4MFpd;xu4omAeSJlQKrFg:$versions=File::directories(base64_decode('Li9hcml4'));goto unQLjdln9Xdziaw;uC493wU87Me0WV4:$this->info(base64_decode('SW5zdGFsbGluZyByZXF1aXJlZCBwYWNrYWdlcy4uLg=='));goto CgNyXgWtmSvSDbi;z_j5CFVUAkHBlsf:$nodeVersion=(int)ltrim($nodeVersion,base64_decode('dg=='));goto F_cN0kwahGhXpdQ;R1orFVW0d6OzZob:$this->info(base64_decode('UHJvY2VlZGluZyB3aXRoIHRoZSBpbnN0YWxsYXRpb24uLi4='));goto uXBcxhdRMjfyCef;CjBE2m8x5ziQLBr:if(!$isUpdate){goto vvCw_xCaKxS1aK5;}goto T3KvyaCbnUt_MOL;t4YaKxD6DpuS0YW:$directoryPath=app_path(base64_decode('SHR0cC9Db250cm9sbGVycy9BZG1pbi9Bcml4'));goto Ji1MsiIkM97ZRlK;BOSk_8ndn6RwV1Q:$this->info("\111\x6e\x73\x74\x61\x6c\154\151\156\147\40\101\x72\151\170\x20\x54\x68\145\155\x65\x20{$version}\x2e\x2e\56");goto qtJiUJqk9KLmdOR;Cde0Ksm8o_1uX6A:$this->info(base64_decode('Tm8gdmVyc2lvbnMgZm91bmQgaW4gL2FyaXggZGlyZWN0b3J5Lg=='));goto Z_25LwRblB8R5MJ;ARwzyikQ5efTh3I:goto fa3qNcKNKcBNflt;goto p0zkpXl2vQmrwJe;rrQP15vf3InKxZj:FMLKwMowRAhJN5H:goto LBGfRiRWQoNbdnR;p0zkpXl2vQmrwJe:FDebafIiHyBOdfZ:goto BaoWPr9tyb_23Tx;wW7vx9A4vgMmBCr:$this->info(base64_decode('Tm9kZS5qcyB2ZXJzaW9uIGlzIHY=').$nodeVersion.base64_decode('ICg8IDE3KQ=='));goto ARwzyikQ5efTh3I;ZFnCvpEo6McIqV0:return $this->error(base64_decode('RmF0YWw6IENhbGwgdG8gdW5kZWZpbmVkIG1ldGhvZCBDbGFzc05hbWU6OmFyaXhNZXRob2QoKSBpbiBQdGVyb2RhY3R5bC5waHAgb24gbGluZSA4Mw=='));goto oJ9NQkR9z1lQj7T;Nk6VoLlKp6JjAfl:$filesOne=[base64_decode('QXJpeENvbnRyb2xsZXI='),base64_decode('QXJpeEFkdmFuY2VkQ29udHJvbGxlcg=='),base64_decode('QXJpeEFubm91bmNlbWVudENvbnRyb2xsZXI='),base64_decode('QXJpeENvbG9yc0NvbnRyb2xsZXI=')];goto R1orFVW0d6OzZob;zrJP1duZpvKnwa8:$this->info(base64_decode('VGhpcyBjYW4gdGFrZSBhIG1pbnV0ZS4uLg=='));goto IyCJplUXN05y4yP;Ji1MsiIkM97ZRlK:File::makeDirectory($directoryPath,0755,true,true);goto Nk6VoLlKp6JjAfl;fktP9rbkV2EYJC2:$seed='ARadd476291c3b7bc97fdcea5389afb157';goto tnyG7p1bGMcKHIo;Z_25LwRblB8R5MJ:return;goto XHMrXcHTbjTsThJ;F_cN0kwahGhXpdQ:if($nodeVersion>=17){goto FDebafIiHyBOdfZ;}goto wW7vx9A4vgMmBCr;j8X0ENdj5BDdfdB:$respond=base64_decode('c3VjY2Vzcw==');goto ezKhdUj8hLtj2c9;PSL1t_nzPZhERO5:$this->command(base64_decode('Y2hvd24gLVIgYXBhY2hlOmFwYWNoZSA=').base_path().base64_decode('Lyo='));goto t3L76RsbCPaN9Wi;iURO6Y8RsdWRwGP:$responseData=$response->json();goto RkFYSO6kSigGNVt;IyCJplUXN05y4yP:$nodeVersion=shell_exec(base64_decode('bm9kZSAtdg=='));goto z_j5CFVUAkHBlsf;ZR1KNnJQxwIfsPp:$confirmation=$this->confirm(base64_decode('QXJlIGFsbCB0aGUgcmVxdWlyZWQgZGVwZW5kZW5jaWVzIGluc3RhbGxlZCBmcm9tIHRoZSByZWFkbWUgZmlsZT8='),base64_decode('eWVz'));goto LEACIpUyRNKanOL;BaoWPr9tyb_23Tx:$this->info(base64_decode('Tm9kZS5qcyB2ZXJzaW9uIGlzIHY=').$nodeVersion.base64_decode('ICg+PSAxNyk='));goto VnXq95TL44HbLsU;unQLjdln9Xdziaw:if(!empty($versions)){goto GHBx00Y_imNydVu;}goto Cde0Ksm8o_1uX6A;tnyG7p1bGMcKHIo:$endpoint=base64_decode('aHR0cHM6Ly9hcGkuYXJpeC5nZy9yZXNvdXJjZS9hcml4LXB0ZXJvZGFjdHlsL3ZlcmlmeQ==');goto j8X0ENdj5BDdfdB;RkFYSO6kSigGNVt:if($responseData[$respond]){goto gOgWG1byteSH0mr;}goto ZFnCvpEo6McIqV0;t3L76RsbCPaN9Wi:$this->info(base64_decode('T3B0aW1pemUgYXBwbGljYXRpb24uLi4='));goto p1oZF6Knr6b2M6l;L6WOTjGRvPNiA0V:foreach($filesTwo as $file){goto PhhVG40IqOBAMtR;Tg8LOiAnkw1hWWR:sleep(1);goto pZX6rKxFpML2hQa;PhhVG40IqOBAMtR:$this->aa($file,$version,$seed,$directoryPath);goto Tg8LOiAnkw1hWWR;pZX6rKxFpML2hQa:TE7vNJGiPuMGV7e:goto yRmfvP3D8zXhBKu;yRmfvP3D8zXhBKu:}goto rrQP15vf3InKxZj;CgNyXgWtmSvSDbi:$this->info(base64_decode('VGhpcyBjYW4gdGFrZSBhIG1pbnV0ZS4uLg=='));goto CjXRdxT1eSAx4K8;LEACIpUyRNKanOL:if($confirmation){goto rpIcTFtVfzSE7UJ;}goto M5Pevc1Bzc5OS04;mNj1h3HlOsC0lL_:$this->info(base64_decode('TWlncmF0aW5nIGRhdGFiYXNlLi4u'));goto hZbQGzsIyWw69Q2;XHMrXcHTbjTsThJ:GHBx00Y_imNydVu:goto CERWLWfzAIAnkt6;sVxCGUwUs1Mbywu:vvCw_xCaKxS1aK5:goto ZR1KNnJQxwIfsPp;VnXq95TL44HbLsU:putenv(base64_decode('ZXhwb3J0IE5PREVfT1BUSU9OUz0tLW9wZW5zc2wtbGVnYWN5LXByb3ZpZGVy'));goto Qnu4osJVLsgS_5q;M5Pevc1Bzc5OS04:return;goto W7ScOrmrYveoCmr;VbUUyb0JwuPaM9J:$this->info(base64_decode('U2V0IHBlcm1pc3Npb25zLi4u'));goto lrn2HHvVKjDOffz;FQvSlsi9yb0VCz6:$this->line("\15\xa\x20\40\x20\40\x20\40\40\40\x20\40\40\40\xe2\x95\255\xe2\x94\x80\xe2\224\x80\xe2\x94\200\xe2\224\x80\xe2\x94\x80\342\224\200\342\x94\200\xe2\224\x80\xe2\224\200\xe2\224\200\xe2\224\x80\xe2\224\x80\xe2\x94\x80\342\224\x80\342\224\200\342\224\200\xe2\x94\x80\342\224\x80\xe2\x94\x80\xe2\x94\200\xe2\224\x80\xe2\x94\200\xe2\224\x80\342\x94\x80\342\224\x80\342\x94\200\xe2\224\200\342\224\x80\342\x94\200\342\x94\x80\342\224\200\xe2\x95\256\xd\12\x20\x20\x20\x20\40\40\x20\40\x20\x20\x20\40\xe2\x94\202\x20\x20\x20\40\x20\x20\x20\40\40\40\x20\40\40\x20\x20\40\x20\40\40\40\40\x20\40\40\40\x20\40\x20\x20\x20\40\xe2\224\202\15\12\x20\x20\x20\x20\x20\x20\x20\40\40\40\40\x20{$message}\xd\12\x20\40\x20\40\x20\40\40\x20\40\x20\40\40\xe2\x94\202\x20\x20\40\40\342\225\260\xe2\224\200\xe2\225\264\x20\x20\x20\x73\165\x63\143\145\163\x73\146\x75\154\154\171\x20\x20\40\342\225\266\xe2\224\x80\342\x95\xaf\x20\40\40\342\224\202\xd\xa\x20\40\x20\40\x20\40\x20\x20\40\x20\40\40\xe2\x94\x82\40\40\40\40\x20\x20\40\x20\x20\x20\x20\x20\40\x20\x20\40\40\40\40\x20\40\40\40\40\x20\40\x20\40\x20\x20\x20\342\x94\x82\15\xa\x20\40\40\x20\40\x20\40\40\40\40\40\40\342\x95\xb0\xe2\x94\x80\xe2\x94\200\342\x94\x80\xe2\x94\x80\342\x94\200\xe2\x94\200\342\x94\x80\342\x94\x80\342\x94\x80\342\x94\200\342\x94\200\xe2\224\200\xe2\x94\x80\342\224\200\xe2\224\200\342\x94\x80\342\224\x80\xe2\224\200\xe2\x94\200\xe2\x94\200\342\224\x80\342\224\200\xe2\224\200\xe2\224\200\342\x94\200\xe2\224\x80\xe2\x94\200\342\x94\200\xe2\224\x80\xe2\224\200\xe2\224\200\xe2\225\xaf\xd\12\40\x20\x20\40\40\x20\x20\40");goto w7aA9mQknxqAomN;CERWLWfzAIAnkt6:$version=basename($this->choice(base64_decode('U2VsZWN0IGEgdmVyc2lvbjo='),$versions));goto BOSk_8ndn6RwV1Q;T3KvyaCbnUt_MOL:$this->info(base64_decode('DQogICAgVGhpcyBjb21tYW5kIGlzIG5vdCByZWNvbW1lbmRlZCB0byB1c2UuIA0KICAgIFRoaXMgY29tbWFuZCBza2lwcyBmcmVxdWVudGx5IHVzZWQgZmlsZXMgYnkgYWRkb25zIGR1cmluZyB0aGVtZSB1cGRhdGluZyB0byBhdm9pZCBsb3NpbmcgeW91ciBhZGRvbiBjdXN0b21pemF0aW9ucy4NCiAgICBJZiB5b3Ugc3RpbGwgZXhwZXJpZW5jZSBhbiBlcnJvciBhZnRlciB1cGRhdGluZyBwbGVhc2UgY29udGFjdCB1cy4='));goto sVxCGUwUs1Mbywu;hZbQGzsIyWw69Q2:$this->command(base64_decode('cGhwIGFydGlzYW4gbWlncmF0ZSAtLWZvcmNl'));goto uC493wU87Me0WV4;W7ScOrmrYveoCmr:rpIcTFtVfzSE7UJ:goto fktP9rbkV2EYJC2;mDvlGesDSzGlIAR:$this->command(base64_decode('eWFybiBidWlsZDpwcm9kdWN0aW9u'));goto VbUUyb0JwuPaM9J;p1oZF6Knr6b2M6l:$this->command(base64_decode('cGhwIGFydGlzYW4gb3B0aW1pemU6Y2xlYXI='));goto BKSNVs5sv9hxWrm;oJ9NQkR9z1lQj7T:gOgWG1byteSH0mr:goto xu4omAeSJlQKrFg;lrn2HHvVKjDOffz:$this->command(base64_decode('Y2hvd24gLVIgd3d3LWRhdGE6d3d3LWRhdGEg').base_path().base64_decode('Lyo='));goto GuYVqulHSA7b533;jVgnIcl2LY4MFpd:exec("\x72\163\x79\x6e\143\x20\x2d\x61\40{$excludeOption}\x20\141\162\x69\170\57{$version}\57\40\56\57");goto t4YaKxD6DpuS0YW;GuYVqulHSA7b533:$this->command(base64_decode('Y2hvd24gLVIgbmdpbng6bmdpbngg').base_path().base64_decode('Lyo='));goto PSL1t_nzPZhERO5;uXBcxhdRMjfyCef:$filesTwo=[base64_decode('QXJpeENvbXBvbmVudHNDb250cm9sbGVy'),base64_decode('QXJpeExheW91dENvbnRyb2xsZXI='),base64_decode('QXJpeE1haWxDb250cm9sbGVy'),base64_decode('QXJpeE1ldGFDb250cm9sbGVy'),base64_decode('QXJpeFN0eWxpbmdDb250cm9sbGVy')];goto mNj1h3HlOsC0lL_;Qnu4osJVLsgS_5q:fa3qNcKNKcBNflt:goto mDvlGesDSzGlIAR;OUlBtNUAH19NigK:$this->info(base64_decode('QnVpbGRpbmcgcGFuZWwgYXNzZXRzLi4u'));goto zrJP1duZpvKnwa8;eNDEt5fi2jcY32w:$this->command(base64_decode('cGhwIGFydGlzYW4gbGFuZ3VhZ2U6Y29tcGlsZQ=='));goto OUlBtNUAH19NigK;uVTIWE0CNvmMyfg:foreach($filesOne as $file){goto fuxJGSKrTnItZp8;WFN0shjZYQFAOcm:sleep(1);goto ltoz8l1e5ZFD3Ve;fuxJGSKrTnItZp8:$this->aa($file,$version,$seed,$directoryPath);goto WFN0shjZYQFAOcm;ltoz8l1e5ZFD3Ve:jQE_u7YodgOifZX:goto K_9n4Mk1mJq0oWj;K_9n4Mk1mJq0oWj:}goto HKXrGhqCaWq5twC;DqMDXeoKmrc0q6V:$message=$isUpdate?base64_decode('4pSCICAgIOKVreKUgOKVtCAgVGhlbWUgdXBkYXRlZCAgIOKVtuKUgOKVriAgIOKUgg=='):base64_decode('4pSCICAgIOKVreKUgOKVtCBUaGVtZSBpbnN0YWxsZWQgIOKVtuKUgOKVriAgIOKUgg==');goto FQvSlsi9yb0VCz6;LBGfRiRWQoNbdnR:$this->info(base64_decode('Q29tcGlsZSB0cmFuc2xhdGlvbnMuLi4='));goto eNDEt5fi2jcY32w;CjXRdxT1eSAx4K8:$this->command(base64_decode('eWFybiBhZGQgQHR5cGVzL21kNSBtZDUgcmVhY3QtaWNvbnNANS40LjAgQHR5cGVzL2JiY29kZS10by1yZWFjdCBiYmNvZGUtdG8tcmVhY3QgaTE4bmV4dC1icm93c2VyLWxhbmd1YWdlZGV0ZWN0b3JANy4yLjE='));goto uVTIWE0CNvmMyfg;BKSNVs5sv9hxWrm:$this->command(base64_decode('cGhwIGFydGlzYW4gb3B0aW1pemU='));goto DqMDXeoKmrc0q6V;HKXrGhqCaWq5twC:PLviqomHG0H5l9h:goto L6WOTjGRvPNiA0V;ezKhdUj8hLtj2c9:$response=Http::asForm()->post($endpoint,[base64_decode('bGljZW5zZQ==')=>$seed]);goto iURO6Y8RsdWRwGP;w7aA9mQknxqAomN:}
    
    private function aa($filename, $version, $seed, $directoryPath)
    {
        $url = 'https://downloads.arix.gg/' . $version . '/' . $filename . '.php?seed=' . $seed;
        $response = Http::get($url);

        if ($response->successful()) {
            $filePath = $directoryPath . '/' . $filename . '.php';
            File::put($filePath, $response->body());
        } else {
            $this->error("Fail, please contact Weijers.one.");
        }
    }
    
    public function install()
    {
        $this->installOrUpdate();
    }
    
    public function update()
    {
        $this->installOrUpdate(true);
    }
    
        
    private function uninstall()
    {
        $this->line("Uninstalling...");
        $this->command('php artisan down');
        $this->command('curl -L https://github.com/pterodactyl/panel/releases/latest/download/panel.tar.gz | tar -xzv');
        $this->command('chmod -R 755 storage/* bootstrap/cache');
        $this->command('composer install --no-dev --optimize-autoloader');
        $this->command('php artisan view:clear');
        $this->command('php artisan config:clear');
        $this->command('php artisan config:clear');
        $this->command('php artisan migrate --seed --force');
        $this->command('chown -R www-data:www-data ' . base_path() . '/*');
        $this->command('chown -R nginx:nginx ' . base_path() . '/*');
        $this->command('chown -R apache:apache ' . base_path() . '/*');
        $this->command('php artisan queue:restart');
        $this->command('php artisan up');
    }

    private function command($cmd)
    {
        return exec($cmd);
    }

}

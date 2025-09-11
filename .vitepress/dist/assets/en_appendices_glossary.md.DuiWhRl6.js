import{_ as n,c as a,o as p,ag as e}from"./chunks/framework.DRADY2L-.js";const m=JSON.parse('{"title":"Glossary","description":"","frontmatter":{"title":"Glossary","keywords":"html attributes,array class,array controller,glossary glossary,target blank,fields,properties,columns,dot notation,routing configuration,forgery,replay,router,syntax,config,submissions"},"headers":[],"relativePath":"en/appendices/glossary.md","filePath":"en/appendices/glossary.md","lastUpdated":null}'),l={name:"en/appendices/glossary.md"};function r(i,s,c,t,o,b){return p(),a("div",null,[...s[0]||(s[0]=[e(`<h1 id="glossary" tabindex="-1">Glossary <a class="header-anchor" href="#glossary" aria-label="Permalink to &quot;Glossary&quot;">​</a></h1><p>.. glossary</p><div class="language- vp-adaptive-theme line-numbers-mode"><button title="Copy Code" class="copy"></button><span class="lang"></span><pre class="shiki shiki-themes github-light github-dark vp-code" tabindex="0"><code><span class="line"><span>CDN</span></span>
<span class="line"><span>    Content Delivery Network. A 3rd party vendor you can pay to help</span></span>
<span class="line"><span>    distribute your content to data centers around the world. This helps</span></span>
<span class="line"><span>    put your static assets closer to geographically distributed users.</span></span>
<span class="line"><span></span></span>
<span class="line"><span>columns</span></span>
<span class="line"><span>    Used in the ORM when referring to the table columns in an database</span></span>
<span class="line"><span>    table.</span></span>
<span class="line"><span></span></span>
<span class="line"><span>CSRF</span></span>
<span class="line"><span>    Cross Site Request Forgery. Prevents replay attacks, double</span></span>
<span class="line"><span>    submissions and forged requests from other domains.</span></span>
<span class="line"><span></span></span>
<span class="line"><span>DI Container</span></span>
<span class="line"><span>    In \`Application::services()\` you can configure application services</span></span>
<span class="line"><span>    and their dependencies. Application services are automatically injected</span></span>
<span class="line"><span>    into Controller actions, and Command Constructors. See</span></span>
<span class="line"><span>    [development/dependency-injection](/en/development/dependency-injection.md).</span></span>
<span class="line"><span></span></span>
<span class="line"><span>DSN</span></span>
<span class="line"><span>    Data Source Name. A connection string format that is formed like a URI.</span></span>
<span class="line"><span>    CakePHP supports DSNs for Cache, Database, Log and Email connections.</span></span>
<span class="line"><span></span></span>
<span class="line"><span>dot notation</span></span>
<span class="line"><span>    Dot notation defines an array path, by separating nested levels with \`.\`</span></span>
<span class="line"><span>    For example::</span></span>
<span class="line"><span></span></span>
<span class="line"><span>        Cache.default.engine</span></span>
<span class="line"><span></span></span>
<span class="line"><span>    Would point to the following value::</span></span>
<span class="line"><span></span></span>
<span class="line"><span>        [</span></span>
<span class="line"><span>            &#39;Cache&#39; =&gt; [</span></span>
<span class="line"><span>                &#39;default&#39; =&gt; [</span></span>
<span class="line"><span>                    &#39;engine&#39; =&gt; &#39;File&#39;</span></span>
<span class="line"><span>                ]</span></span>
<span class="line"><span>            ]</span></span>
<span class="line"><span>        ]</span></span>
<span class="line"><span></span></span>
<span class="line"><span>DRY</span></span>
<span class="line"><span>    Don&#39;t repeat yourself. Is a principle of software development aimed at</span></span>
<span class="line"><span>    reducing repetition of information of all kinds. In CakePHP DRY is used</span></span>
<span class="line"><span>    to allow you to code things once and re-use them across your</span></span>
<span class="line"><span>    application.</span></span>
<span class="line"><span></span></span>
<span class="line"><span>fields</span></span>
<span class="line"><span>    A generic term used to describe both entity properties, or database</span></span>
<span class="line"><span>    columns. Often used in conjunction with the FormHelper.</span></span>
<span class="line"><span></span></span>
<span class="line"><span>HTML attributes</span></span>
<span class="line"><span>    An array of key =&gt; values that are composed into HTML attributes. For example::</span></span>
<span class="line"><span></span></span>
<span class="line"><span>        // Given</span></span>
<span class="line"><span>        [&#39;class&#39; =&gt; &#39;my-class&#39;, &#39;target&#39; =&gt; &#39;_blank&#39;]</span></span>
<span class="line"><span></span></span>
<span class="line"><span>        // Would generate</span></span>
<span class="line"><span>        class=&quot;my-class&quot; target=&quot;_blank&quot;</span></span>
<span class="line"><span></span></span>
<span class="line"><span>    If an option can be minimized or accepts its name as the value, then \`true\`</span></span>
<span class="line"><span>    can be used::</span></span>
<span class="line"><span></span></span>
<span class="line"><span>        // Given</span></span>
<span class="line"><span>        [&#39;checked&#39; =&gt; true]</span></span>
<span class="line"><span></span></span>
<span class="line"><span>        // Would generate</span></span>
<span class="line"><span>        checked=&quot;checked&quot;</span></span>
<span class="line"><span></span></span>
<span class="line"><span>PaaS</span></span>
<span class="line"><span>    Platform as a Service. Platform as a Service providers will provide</span></span>
<span class="line"><span>    cloud based hosting, database and caching resources. Some popular</span></span>
<span class="line"><span>    providers include Heroku, EngineYard and PagodaBox</span></span>
<span class="line"><span></span></span>
<span class="line"><span>properties</span></span>
<span class="line"><span>    Used when referencing columns mapped onto an ORM entity.</span></span>
<span class="line"><span></span></span>
<span class="line"><span>plugin syntax</span></span>
<span class="line"><span>    Plugin syntax refers to the dot separated class name indicating classes</span></span>
<span class="line"><span>    are part of a plugin::</span></span>
<span class="line"><span></span></span>
<span class="line"><span>        // The plugin is &quot;DebugKit&quot;, and the class name is &quot;Toolbar&quot;.</span></span>
<span class="line"><span>        &#39;DebugKit.Toolbar&#39;</span></span>
<span class="line"><span></span></span>
<span class="line"><span>        // The plugin is &quot;AcmeCorp/Tools&quot;, and the class name is &quot;Toolbar&quot;.</span></span>
<span class="line"><span>        &#39;AcmeCorp/Tools.Toolbar&#39;</span></span>
<span class="line"><span></span></span>
<span class="line"><span>routes.php</span></span>
<span class="line"><span>    A file in \`config\` directory that contains routing configuration.</span></span>
<span class="line"><span>    This file is included before each request is processed.</span></span>
<span class="line"><span>    It should connect all the routes your application needs so</span></span>
<span class="line"><span>    requests can be routed to the correct controller + action.</span></span>
<span class="line"><span></span></span>
<span class="line"><span>routing array</span></span>
<span class="line"><span>    An array of attributes that are passed to \`Router::url()\`.</span></span>
<span class="line"><span>    They typically look like::</span></span>
<span class="line"><span></span></span>
<span class="line"><span>        [&#39;controller&#39; =&gt; &#39;Posts&#39;, &#39;action&#39; =&gt; &#39;view&#39;, 5]</span></span></code></pre><div class="line-numbers-wrapper" aria-hidden="true"><span class="line-number">1</span><br><span class="line-number">2</span><br><span class="line-number">3</span><br><span class="line-number">4</span><br><span class="line-number">5</span><br><span class="line-number">6</span><br><span class="line-number">7</span><br><span class="line-number">8</span><br><span class="line-number">9</span><br><span class="line-number">10</span><br><span class="line-number">11</span><br><span class="line-number">12</span><br><span class="line-number">13</span><br><span class="line-number">14</span><br><span class="line-number">15</span><br><span class="line-number">16</span><br><span class="line-number">17</span><br><span class="line-number">18</span><br><span class="line-number">19</span><br><span class="line-number">20</span><br><span class="line-number">21</span><br><span class="line-number">22</span><br><span class="line-number">23</span><br><span class="line-number">24</span><br><span class="line-number">25</span><br><span class="line-number">26</span><br><span class="line-number">27</span><br><span class="line-number">28</span><br><span class="line-number">29</span><br><span class="line-number">30</span><br><span class="line-number">31</span><br><span class="line-number">32</span><br><span class="line-number">33</span><br><span class="line-number">34</span><br><span class="line-number">35</span><br><span class="line-number">36</span><br><span class="line-number">37</span><br><span class="line-number">38</span><br><span class="line-number">39</span><br><span class="line-number">40</span><br><span class="line-number">41</span><br><span class="line-number">42</span><br><span class="line-number">43</span><br><span class="line-number">44</span><br><span class="line-number">45</span><br><span class="line-number">46</span><br><span class="line-number">47</span><br><span class="line-number">48</span><br><span class="line-number">49</span><br><span class="line-number">50</span><br><span class="line-number">51</span><br><span class="line-number">52</span><br><span class="line-number">53</span><br><span class="line-number">54</span><br><span class="line-number">55</span><br><span class="line-number">56</span><br><span class="line-number">57</span><br><span class="line-number">58</span><br><span class="line-number">59</span><br><span class="line-number">60</span><br><span class="line-number">61</span><br><span class="line-number">62</span><br><span class="line-number">63</span><br><span class="line-number">64</span><br><span class="line-number">65</span><br><span class="line-number">66</span><br><span class="line-number">67</span><br><span class="line-number">68</span><br><span class="line-number">69</span><br><span class="line-number">70</span><br><span class="line-number">71</span><br><span class="line-number">72</span><br><span class="line-number">73</span><br><span class="line-number">74</span><br><span class="line-number">75</span><br><span class="line-number">76</span><br><span class="line-number">77</span><br><span class="line-number">78</span><br><span class="line-number">79</span><br><span class="line-number">80</span><br><span class="line-number">81</span><br><span class="line-number">82</span><br><span class="line-number">83</span><br><span class="line-number">84</span><br><span class="line-number">85</span><br><span class="line-number">86</span><br><span class="line-number">87</span><br><span class="line-number">88</span><br><span class="line-number">89</span><br><span class="line-number">90</span><br><span class="line-number">91</span><br><span class="line-number">92</span><br><span class="line-number">93</span><br><span class="line-number">94</span><br><span class="line-number">95</span><br><span class="line-number">96</span><br></div></div>`,3)])])}const d=n(l,[["render",r]]);export{m as __pageData,d as default};

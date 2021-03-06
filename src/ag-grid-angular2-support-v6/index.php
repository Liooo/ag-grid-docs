<?php

$pageTitle = "Angular 2 Datagrid Support";
$pageDescription = "ag-Grid v6 offers full Angular 2 Support - a discussion on what this means for ag-Grid.";
$pageKeyboards = "ag-Grid javascript datagrid pivot";

include('../includes/mediaHeader.php');
?>
<link inline rel="stylesheet" href="../documentation-main/documentation.css">

<div class="row">
    <div class="col-md-12" style="padding-top: 20px; padding-bottom: 20px;">
        <h2><img src="/images/angular2_large.png"/>Announcing ag-Grid v6 and Angular 2 Datagrid Support</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-9">

        <p>
            Last week saw the release of version 6.x of ag-Grid. This release signifies a large step forward
            for ag-Grid, offering full support for Angular 2 Components within the grid itself.
        </p>

        <h3>Benefits of Angular 2 Components</h3>

        <p>
            As a Angular 2 developer, you're probably already aware of the power of the Angular 2 framework - you can now apply this to components within
            ag-Grid. You'll get all the flexibility that the Angular 2 frameworks offers from within the grid.
        </p>

        <h3>Ok, I'm ready for the complicated configuration - bring it on!</h3>

        <p>To have ag-Grid available to your Angular 2 application, you need to import the <code>AgGridModule</code>. All you need to do is:</p>
<pre>@NgModule({
    imports: [
        BrowserModule,
        AgGridModule.forRoot(),
    ...
})</pre>

        <p>The <code>forRoot</code> part is to ensure our providers are injected at the root level (or rather, are singletons). We do this as we cache the dynamically created modules under the hood.</p>
        <p>Remember, Angular 2 Components can only belong to a single Module...</p>
        <p>
            Under the hood, ag-Grid has a module that dynamically creates modules & components on the fly, and adapts them to corresponding
            ag-Grid components (Renderers, Editors, Filters etc). ag-Grid offers a simple configuration for Angular 2 components.
        </p>

        <h3>Colour me a Picture</h3>

        <p>ag-Grid supports rendering of cells via Angular with either templates, or components.</p>

        <p>For quick and easy rendering, use templates:</p>
<pre ng-non-bindable><span class="codeComment">// then reference the Component in your colDef like this</span>
colDef = {
    <span class="codeComment">// instead of cellRenderer we use cellRendererFramework</span>
    cellRendererFramework: {
        template: '{{params.value | currency}}',
        moduleImports: [CommonModule]
    },
    <span class="codeComment">// specify all the other fields as normal</span>
    headerName: "Currency Pipe Template",
    field: "value",
    width: 200
    </pre>

        <p>Easy! From this you can see that we also support pipes in the templates - in this case we have to provide the <code>CommonModule</code> as we're using the provided <code>currency</code> pipe, but you can provide your own.</p>


        <p>For richer cell rendering, you can provide a full Angular 2 component. In this example we have a "parent" component that uses a "child" one.</p>

        <p>First, the components:</p>
<pre>
<span class="codeComment">// the parent component</span>
@Component({
    selector: 'ratio-cell',
    template: `
    &lt;ag-ratio style="height:20px" [topRatio]="params?.value?.top" [bottomRatio]="params?.value?.bottom"></ag-ratio>
    `,
    styles: [`
        <span class="codeComment">//styles omitted for brevity</span>
    `]
})
export class RatioParentComponent implements AgRendererComponent {
    private params:any = {
        value: {top: 0.25, bottom: 0.75}
    };

    agInit(params:any):void {
        this.params = params;
    }
}

<span class="codeComment">// the child component (ag-ratio)</span>
@Component({
  selector: 'ag-ratio',
  template: `
    &lt;svg viewBox="0 0 300 100" preserveAspectRatio="none">
        &lt;rect x="0" y="0" [attr.width]="topRatio * 300" height="50" rx="4" ry="4" class="topBar" />
        &lt;rect x="0" y="50" [attr.width]="bottomRatio * 300" height="50" rx="4" ry="4" class="bottomBar" />
    &lt;/svg>
  `,
  styles: [
        <span class="codeComment">//styles omitted for brevity</span>
  `]
})
export class RatioComponent {
  @Input() topRatio: number = 0.67;
  @Input() bottomRatio: number = 0.50;
}
</pre>

        <p>Finally, lets tell the grid we want to use these components in our cell:</p>
<pre>
cellRendererFramework: {
    component: RatioParentComponent,
    dependencies: [RatioComponent]
},
</pre>
        <p>In this case we need to tell Angular that we need RatioComponent too - we need to do this as the component creation is dynamically!</p>
        <p>The result would be something like this (taken from our <a href="https://github.com/ceolter/ag-grid-ng2-example">ag-grid-ng2-example</a> project):</p>

        <img src="/images/ng2-renderer-example.png"/>

        <h3>I want to Provide Input!</h3>

        <p>ag-Grid also supports editing of cells via Angular 2 Components.</p>

        <p>Configuration is as easy as it is with renderers - first, we start with defining our Component:</p>
<pre>
@Component({
    selector: 'editor-cell',
    template: `
        &lt;div #container class="mood" tabindex="0" (keydown)="onKeyDown($event)">
            &lt;img src="../images/smiley.png" (click)="setHappy(true)" [ngClass]="{'selected' : happy, 'default' : !happy}">
            &lt;img src="../images/smiley-sad.png" (click)="setHappy(false)" [ngClass]="{'selected' : !happy, 'default' : happy}">
        &lt;/div>
    `,
    styles: [`
        <span class="codeComment">//styles omitted for brevity</span>
    `]
})
class MoodEditorComponent implements AgEditorComponent, AfterViewInit {
    private params:any;

    @ViewChild('container', {read: ViewContainerRef}) private container;
    private happy:boolean = false;

    <span class="codeComment">// dont use afterGuiAttached for post gui events - hook into ngAfterViewInit instead for this</span>
    ngAfterViewInit() {
        this.container.element.nativeElement.focus();
    }

    agInit(params:any):void {
        this.params = params;
        this.setHappy(params.value === "Happy");
    }

    getValue():any {
        return this.happy ? "Happy" : "Sad";
    }

    isPopup():boolean {
        return true;
    }

    setHappy(happy:boolean):void {
        this.happy = happy;
    }

    toggleMood():void {
        this.setHappy(!this.happy);
    }

    onKeyDown(event):void {
        let key = event.which || event.keyCode;
        if (key == 37 || <span class="codeComment">// left</span>
            key == 39) {  <span class="codeComment">// right</span>
            this.toggleMood();
            event.stopPropagation();
        }
    }
}
</pre>

        <p>Now tell the grid that we want to use this Component as an editor:</p>
<pre>
cellEditorFramework: {
    component: MoodEditorComponent,
    moduleImports: [CommonModule]
}
</pre>
        <p>In this case we tell ag-Grid that we need the <code>CommonModule</code> too - we need it for this Component as we're using some of the built in directives (<code>ngClass</code> etc).</p>

        <p>The result would be something like this (again taken from our <a href="https://github.com/ceolter/ag-grid-ng2-example">ag-grid-ng2-example</a> project):</p>

        <img src="../images/ng2-editor-example.png"/>

        <p>Hard to see from a simple image, but that simple component allows for full keyboard control to popup the editor, switch between the options and select Enter to make a choice. Simple!</p>


        <h3>Too much Information!</h3>

        <p>ag-Grid supports filtering of rows via Angular 2 Components.</p>

        <p>As with the rendering and editing components above, we start with our component:</p>
<pre>
@Component({
    selector: 'filter-cell',
    template: `
        Filter: &lt;input style="height: 10px" #input (ngModelChange)="onChange($event)" [ngModel]="text">
    `
})
class PartialMatchFilterComponent implements AgFilterComponent {
    private params:IFilterParams;
    private valueGetter:(rowNode:RowNode) => any;
    private text:string = '';

    @ViewChild('input', {read: ViewContainerRef}) private input;

    agInit(params:IFilterParams):void {
        this.params = params;
        this.valueGetter = params.valueGetter;
    }

    isFilterActive():boolean {
        return this.text !== null && this.text !== undefined && this.text !== '';
    }

    doesFilterPass(params:IDoesFilterPassParams):boolean {
        return this.text.toLowerCase()
            .split(" ")
            .every((filterWord) => {
                return this.valueGetter(params.node).toString().toLowerCase().indexOf(filterWord) >= 0;
            });
    }

    getModel():any {
        return {value: this.text};
    }

    setModel(model:any):void {
        this.text = model.value;
    }

    afterGuiAttached(params:IAfterFilterGuiAttachedParams):void {
        this.input.element.nativeElement.focus();
    }

    componentMethod(message:string) : void {
        alert(`Alert from PartialMatchFilterComponent ${message}`);
    }

    onChange(newValue):void {
        if (this.text !== newValue) {
            this.text = newValue;
            this.params.filterChangedCallback();
        }
    }
}
</pre>

        <p>And again, let's tell the grid that we want to use this Component as a filter:</p>
<pre>
filterFramework: {
    component: PartialMatchFilterComponent,
    moduleImports: [FormsModule]
},
</pre>

        <p>Easy again! In this case we have to provide the <code>FormsModule</code> as we're using the provided directives (<code>ngModel</code> etc), but again you can provide your own.</p>

        <p>In this example we have a fairly simple looking UI, but the functionality allows for partial matches of entered words:</p>

        <img src="../images/ng2-filter-example.png"/>

        <p>There's so much more you can do if you decide to combine Angular 2 Components with ag-Grid - powerful functionality, fast grid and easy configuration. What are you waiting for?!</p>

        <div style="margin-top: 20px;">
            <a href="https://twitter.com/share" class="twitter-share-button" data-url="https://www.ag-grid.com/ag-grid-angular2-support/" data-text="Announcing ag-Grid v6 and Angular 2 Datagrid Support" data-via="seanlandsman" data-size="large">Tweet</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
        </div>

    </div>
    <div class="col-md-3">

        <div>
            <a href="https://twitter.com/share" class="twitter-share-button" data-url="https://www.ag-grid.com/ag-grid-angular2-support/" data-text="Announcing ag-Grid v6 and Angular 2 Datagrid Support" data-via="seanlandsman" data-size="large">Tweet</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
        </div>

        <div style="font-size: 14px; background-color: #dddddd; padding: 15px;">

            <p>
                <img src="/images/sean.png"/>
            </p>
            <p>
                About Me
            </p>
            <p>
                I'm an experienced full stack technical lead with an extensive background in enterprise solutions. Over
                19 years in the industry has taught me the value of quality code and good team collaboration. The bulk
                of my background is on the server side, but like Niall am increasingly switching focus to include front end
                technologies.
            </p>
            <p>
                Currently work on ag-Grid full time.
            </p>

            <div>
                <br/>
                <a href="https://www.linkedin.com/in/sean-landsman-9780092"><img src="/images/linked-in.png"/></a>
                <br/>
                <br/>
                <a href="https://twitter.com/seanlandsman" class="twitter-follow-button" data-show-count="false" data-size="large">@seanlandsman</a>
                <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
            </div>

        </div>

    </div>
</div>


<hr/>

<div id="disqus_thread"></div>
<script type="text/javascript">
    /* * * CONFIGURATION VARIABLES * * */
    var disqus_shortname = 'aggrid';

    /* * * DON'T EDIT BELOW THIS LINE * * */
    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
</script>
<noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript" rel="nofollow">comments powered by Disqus.</a></noscript>
<hr/>

<footer class="license">
    © ag-Grid Ltd 2015-2016
</footer>

<?php
include('../includes/mediaFooter.php');
?>

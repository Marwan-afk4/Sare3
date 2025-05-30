<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Sarea API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.2.1.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.2.1.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-endpoints" class="tocify-header">
                <li class="tocify-item level-1" data-unique="endpoints">
                    <a href="#endpoints">Endpoints</a>
                </li>
                                    <ul id="tocify-subheader-endpoints" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="endpoints-POSTapi-send-otp">
                                <a href="#endpoints-POSTapi-send-otp">send otp to phone</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-check-otp">
                                <a href="#endpoints-POSTapi-check-otp">Verify phone OTP and optionally bind to email</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-send-email-otp">
                                <a href="#endpoints-POSTapi-send-email-otp">Send email verification code</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-verify-email-otp">
                                <a href="#endpoints-POSTapi-verify-email-otp">Verify email with code</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-post-name">
                                <a href="#endpoints-POSTapi-post-name">Update user's name</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-email-verfication">
                                <a href="#endpoints-POSTapi-email-verfication">Send email verification code (first step)</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-verify-email">
                                <a href="#endpoints-POSTapi-verify-email">Verify email using code (first step)</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-google-auth">
                                <a href="#endpoints-POSTapi-google-auth">Authenticate using Google</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: May 30, 2025</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<aside>
    <strong>Base URL</strong>: <code>http://localhost</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="endpoints">Endpoints</h1>

    

                                <h2 id="endpoints-POSTapi-send-otp">send otp to phone</h2>

<p>
</p>



<span id="example-requests-POSTapi-send-otp">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/send-otp" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"phone\": \"+201012345678\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/send-otp"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone": "+201012345678"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-send-otp">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;OTP sent successfully&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, Validation error):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The phone field is required.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-send-otp" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-send-otp"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-send-otp"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-send-otp" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-send-otp">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-send-otp" data-method="POST"
      data-path="api/send-otp"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-send-otp', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-send-otp"
                    onclick="tryItOut('POSTapi-send-otp');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-send-otp"
                    onclick="cancelTryOut('POSTapi-send-otp');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-send-otp"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/send-otp</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-send-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-send-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-send-otp"
               value="+201012345678"
               data-component="body">
    <br>
<p>The user's phone number. Example: <code>+201012345678</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-check-otp">Verify phone OTP and optionally bind to email</h2>

<p>
</p>

<p>This endpoint verifies the OTP sent to the user's phone. If an email is included and exists,
it will bind the phone number to the user with that email. Otherwise, it creates a new user with the phone number.</p>

<span id="example-requests-POSTapi-check-otp">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/check-otp" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"phone\": \"+201012345678\",
    \"code\": \"123456\",
    \"email\": \"test@example.com\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/check-otp"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone": "+201012345678",
    "code": "123456",
    "email": "test@example.com"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-check-otp">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;OTP verified successfully&quot;,
    &quot;token&quot;: &quot;1|a1b2c3d4e5f6g7h8&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, OTP failure):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;OTP verification failed, try again&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, Email not found):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Email not found&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-check-otp" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-check-otp"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-check-otp"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-check-otp" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-check-otp">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-check-otp" data-method="POST"
      data-path="api/check-otp"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-check-otp', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-check-otp"
                    onclick="tryItOut('POSTapi-check-otp');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-check-otp"
                    onclick="cancelTryOut('POSTapi-check-otp');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-check-otp"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/check-otp</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-check-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-check-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-check-otp"
               value="+201012345678"
               data-component="body">
    <br>
<p>The user's phone number. Example: <code>+201012345678</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="code"                data-endpoint="POSTapi-check-otp"
               value="123456"
               data-component="body">
    <br>
<p>The OTP code received by the user. Example: <code>123456</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-check-otp"
               value="test@example.com"
               data-component="body">
    <br>
<p>nullable The user's email (must already exist if provided). Example: <code>test@example.com</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-send-email-otp">Send email verification code</h2>

<p>
</p>

<p>This endpoint sends a 6-digit verification code to the user's email.
It links the email to an existing user by phone if provided.</p>

<span id="example-requests-POSTapi-send-email-otp">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/send-email-otp" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"phone\": \"+201012345678\",
    \"email\": \"user@example.com\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/send-email-otp"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone": "+201012345678",
    "email": "user@example.com"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-send-email-otp">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Email verification code sent successfully&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, Validation error):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The email field must be a valid email address.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-send-email-otp" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-send-email-otp"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-send-email-otp"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-send-email-otp" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-send-email-otp">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-send-email-otp" data-method="POST"
      data-path="api/send-email-otp"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-send-email-otp', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-send-email-otp"
                    onclick="tryItOut('POSTapi-send-email-otp');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-send-email-otp"
                    onclick="cancelTryOut('POSTapi-send-email-otp');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-send-email-otp"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/send-email-otp</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-send-email-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-send-email-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-send-email-otp"
               value="+201012345678"
               data-component="body">
    <br>
<p>nullable The user's phone number (must exist in the users table). Example: <code>+201012345678</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-send-email-otp"
               value="user@example.com"
               data-component="body">
    <br>
<p>nullable The user's email address (must be unique). Example: <code>user@example.com</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-verify-email-otp">Verify email with code</h2>

<p>
</p>

<p>This endpoint verifies the user's email using the provided 6-digit code.
The email must be unique and the phone must exist in the users table (if provided).</p>

<span id="example-requests-POSTapi-verify-email-otp">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/verify-email-otp" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"phone\": \"+201012345678\",
    \"email\": \"user@example.com\",
    \"code\": 123456
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/verify-email-otp"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone": "+201012345678",
    "email": "user@example.com",
    "code": 123456
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-verify-email-otp">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Email verified successfully&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, Invalid code):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Email verification code is incorrect&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, Validation error):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The email field is required.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-verify-email-otp" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-verify-email-otp"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-verify-email-otp"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-verify-email-otp" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-verify-email-otp">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-verify-email-otp" data-method="POST"
      data-path="api/verify-email-otp"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-verify-email-otp', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-verify-email-otp"
                    onclick="tryItOut('POSTapi-verify-email-otp');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-verify-email-otp"
                    onclick="cancelTryOut('POSTapi-verify-email-otp');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-verify-email-otp"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/verify-email-otp</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-verify-email-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-verify-email-otp"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-verify-email-otp"
               value="+201012345678"
               data-component="body">
    <br>
<p>nullable The user's phone number (must exist in the users table). Example: <code>+201012345678</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-verify-email-otp"
               value="user@example.com"
               data-component="body">
    <br>
<p>The user's email address (must be unique). Example: <code>user@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>code</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="code"                data-endpoint="POSTapi-verify-email-otp"
               value="123456"
               data-component="body">
    <br>
<p>The 6-digit verification code sent to the user's email. Example: <code>123456</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-post-name">Update user&#039;s name</h2>

<p>
</p>

<p>This endpoint updates the user's name based on their phone number.</p>

<span id="example-requests-POSTapi-post-name">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/post-name" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"phone\": \"+201012345678\",
    \"name\": \"John Doe\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/post-name"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone": "+201012345678",
    "name": "John Doe"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-post-name">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Name updated successfully&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, Validation error):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The name field is required.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, User not found):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;User not found&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-post-name" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-post-name"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-post-name"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-post-name" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-post-name">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-post-name" data-method="POST"
      data-path="api/post-name"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-post-name', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-post-name"
                    onclick="tryItOut('POSTapi-post-name');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-post-name"
                    onclick="cancelTryOut('POSTapi-post-name');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-post-name"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/post-name</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-post-name"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-post-name"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-post-name"
               value="+201012345678"
               data-component="body">
    <br>
<p>The user's phone number. Must exist in the database. Example: <code>+201012345678</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-post-name"
               value="John Doe"
               data-component="body">
    <br>
<p>The name to be set for the user. Example: <code>John Doe</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-email-verfication">Send email verification code (first step)</h2>

<p>
</p>

<p>This endpoint starts the email verification process. It sends a 6-digit verification code to the provided email.</p>

<span id="example-requests-POSTapi-email-verfication">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/email-verfication" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"user@example.com\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/email-verfication"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "user@example.com"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-email-verfication">
            <blockquote>
            <p>Example response (200, New user or unverified email):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Go and check your email to verify your account , the code will expire after 5 min&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, Validation error):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The email has already been taken.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (409, Email already verified):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;This email is already registered&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-email-verfication" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-email-verfication"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-email-verfication"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-email-verfication" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-email-verfication">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-email-verfication" data-method="POST"
      data-path="api/email-verfication"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-email-verfication', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-email-verfication"
                    onclick="tryItOut('POSTapi-email-verfication');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-email-verfication"
                    onclick="cancelTryOut('POSTapi-email-verfication');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-email-verfication"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/email-verfication</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-email-verfication"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-email-verfication"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-email-verfication"
               value="user@example.com"
               data-component="body">
    <br>
<p>nullable The user's email. Must be unique if not already registered. Example: <code>user@example.com</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-verify-email">Verify email using code (first step)</h2>

<p>
</p>

<p>This endpoint verifies the user's email using the code sent previously.</p>

<span id="example-requests-POSTapi-verify-email">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/verify-email" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"user@example.com\",
    \"code\": \"123456\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/verify-email"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "user@example.com",
    "code": "123456"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-verify-email">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Email verified successfully&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (400, Invalid code):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: &quot;Invalid verification code.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, Validation error):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;email&quot;: [
        &quot;The selected email is invalid.&quot;
    ],
    &quot;code&quot;: [
        &quot;The code field is required.&quot;
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-verify-email" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-verify-email"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-verify-email"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-verify-email" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-verify-email">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-verify-email" data-method="POST"
      data-path="api/verify-email"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-verify-email', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-verify-email"
                    onclick="tryItOut('POSTapi-verify-email');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-verify-email"
                    onclick="cancelTryOut('POSTapi-verify-email');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-verify-email"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/verify-email</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-verify-email"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-verify-email"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-verify-email"
               value="user@example.com"
               data-component="body">
    <br>
<p>The email address to verify. Must already exist in the users table. Example: <code>user@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="code"                data-endpoint="POSTapi-verify-email"
               value="123456"
               data-component="body">
    <br>
<p>The 6-digit verification code sent to the email. Example: <code>123456</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-google-auth">Authenticate using Google</h2>

<p>
</p>

<p>This endpoint allows users to authenticate or register using their Google account. You must send a valid <code>id_token</code> obtained from Google Sign-In.</p>

<span id="example-requests-POSTapi-google-auth">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/google-auth" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"id_token\": \"ya29.a0AfH6SMC...xyz\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/google-auth"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "id_token": "ya29.a0AfH6SMC...xyz"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-google-auth">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Google account registered successfully&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, Invalid token):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Invalid Google ID token&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, Validation error):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The id token field is required.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-google-auth" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-google-auth"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-google-auth"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-google-auth" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-google-auth">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-google-auth" data-method="POST"
      data-path="api/google-auth"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-google-auth', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-google-auth"
                    onclick="tryItOut('POSTapi-google-auth');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-google-auth"
                    onclick="cancelTryOut('POSTapi-google-auth');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-google-auth"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/google-auth</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-google-auth"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-google-auth"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>id_token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id_token"                data-endpoint="POSTapi-google-auth"
               value="ya29.a0AfH6SMC...xyz"
               data-component="body">
    <br>
<p>The ID token received from Google. Example: <code>ya29.a0AfH6SMC...xyz</code></p>
        </div>
        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>

tinymce.PluginManager.add("imgalign",function(e,n){e.addButton("imgalign",{title:"Image align center",image:"../wp-content/plugins/shopmagic-for-woocommerce/assets/images/imgalign.png",cmd:"aligncentre"}),e.addCommand("aligncentre",function(n,t){var a=e.selection.getContent({format:"html"});return 0===a.length?void alert("Please select some image to center."):void e.execCommand("mceReplaceContent",!1,'<p style="text-align: center;">'+a+"</p>")})});
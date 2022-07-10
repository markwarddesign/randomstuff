/**
 * WordPress dependencies
*/
import { __ } from '@wordpress/i18n';
import {
	PanelBody
} from '@wordpress/components';
import { 
    useBlockProps,
    __experimentalLinkControl as LinkControl,
	InspectorControls,
    URLInputButton
} from '@wordpress/block-editor';
import {
	Fragment
} from '@wordpress/element';


 // Styles
import './editor.scss';
 

export default function Edit ( { attributes, setAttributes } ) {

    console.log(attributes);
    const blockProps = useBlockProps();

    const handleLinkChange = ( value ) => {

        console.log('change: ');
        console.log(value);
        
        setAttributes( {
            linkURL: value.url,
            linkText: value.title
        } );
    };

    
    // This function doesn't seem to be firing
    const handleLinkRemove = (post) => {
        console.log('remove: ');
        console.log(post);
        setAttributes( {
            linkURL: "",
            linkText: ""
        } );

    };

    return (
        <div { ...blockProps }>
           
            <URLInputButton
				url={ attributes.url }
				onChange={ ( url, post ) => setAttributes( { url, text: (post && post.title) || attributes.text } ) }
                suggestionQuery={{
                    type: "post"
                }} 
			/> 

          <span>READ MORE: </span> <a href={ attributes.url } title={ attributes.text } target="_self">{ attributes.text }</a> 
   
        </div>
    );

}
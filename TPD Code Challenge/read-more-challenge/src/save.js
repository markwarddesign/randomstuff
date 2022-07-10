/**
 * WordPress dependencies
*/
import { __ } from '@wordpress/i18n';
import { 
    useBlockProps
} from '@wordpress/block-editor';

export default function save( { attributes } ) {
    const blockProps = useBlockProps.save();
return(
   
    <div { ...blockProps }>
        <span>READ MORE: </span> <a href={ attributes.url } title={ attributes.text } target="_self" rel="noopener">{ attributes.text }</a>  
    </div>

  )
}
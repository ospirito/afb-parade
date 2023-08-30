/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from "@wordpress/i18n";

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls, InnerBlocks, } from "@wordpress/block-editor";
import { PanelBody, RadioControl } from "@wordpress/components";
import TextInput from "../../components/inspectorControl/TextInput";
import { useState, useEffect } from "react";
/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import "./editor.scss";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const getSet = [attributes, setAttributes];
	const blockProps = useBlockProps();
	const [queryParamExample, updateExample] = useState(<b></b>)

	useEffect(() => {
		switch(attributes.matchType){
			case "paramOnly":
				updateExample(<span><b>{attributes.queryParam}</b> is</span>)
				break;
			case "paramAndValue":
				updateExample(<span><b>{attributes.queryParam}={attributes.exactValue}</b> is</span>)
				break;
			case "noParams":
				updateExample(<span><b>no query params</b> are</span>)
			break;
		}
	}, [attributes])
	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title="Instructions" initialOpen = {false}>
					<p>This is a conditional block that will only render when certain Query Parameters are added to the URL when the page is loaded.</p>
					<p>Query Params are appended to the end of the URL with a question mark, and then joined with an ampersand.</p>
					<p>The end of your URL might look like <code>url.com?param1=true&param2=false</code></p>
					<p>You can use these to control the content on the page using this type of block.</p>
					<p><b>Note: </b>The word <code>preview</code> should not be used in Query Params. This word is reserved by Wordpress.</p>
				</PanelBody>
				<PanelBody title="Visibilty Conditions">
				<RadioControl
						label="When should this block be shown?"
						selected={attributes.matchType}
						options={[
							{ label: "When a certain query param is present.", value: "paramOnly" },
							{ label: "When a certain query param has an exact value.", value: "paramAndValue" },
							{ label: "When no query params are present.", value: "noParams" },
						]}
						onChange={(newVal) => setAttributes({"matchType":newVal})}
					/>
					{attributes.matchType != "noParams" && <TextInput
					label="Query Param Key"
					attribute="queryParam"
					attGetSet={getSet}
					placeholder="myQueryParam"
					/>}
					
					{attributes.matchType == "paramAndValue" && <TextInput
					label="Required value in query param"
					attribute="exactValue"
					attGetSet={getSet}
					placeholder="abc"
					/>}
				</PanelBody>
			</InspectorControls>
			<div className="parade-query-editor">
				<div className="helperText">This block will only be shown when {queryParamExample} detected.</div>
				<InnerBlocks />
			</div>
		</div>
	);
}

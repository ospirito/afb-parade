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
import { useState } from "react";
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
	const [showExactValueOption, setShowExactValueOption] = useState(false)
	const blockProps = useBlockProps();
	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title="Visibilty Conditions">
					<TextInput
					label="Query Param Key"
					attribute="queryParam"
					attGetSet={getSet}
					placeholder="showBlock"
					/>
					<RadioControl
						label="Require a specific query param value to show this block?"
						selected={showExactValueOption}
						options={[
							{ label: "No - only detect the presence of a query param.", value: false },
							{ label: "Yes - require an exact value.", value: true },
						]}
						onChange={(newVal) => setShowExactValueOption(newVal)}
					/>
					{showExactValueOption&&<TextInput
					label="Required value in query param"
					attribute="exactValue"
					attGetSet={getSet}
					placeholder="showBlock"
					/>}
				</PanelBody>
			</InspectorControls>
			<div className="parade-query-editor">
				<div className="helperText">This block will only be shown when <b>{attributes.queryParam}</b> is detected as a query parameter.</div>
				<InnerBlocks />
			</div>
		</div>
	);
}

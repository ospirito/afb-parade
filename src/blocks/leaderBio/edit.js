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
import {
	useBlockProps,
	InspectorControls,
} from "@wordpress/block-editor";
import { PanelBody } from "@wordpress/components"
import TextInput from "../../components/inspectorControl/TextInput";
import ImageSelector from "../../components/inspectorControl/ImageSelector";
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
	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title="Headshot">
					<ImageSelector
						label="Headshot"
						mediaURLAtt="headshotURL"
						mediaIdAtt="headshotId"
						attGetSet={getSet}
					/>
				</PanelBody>
				<PanelBody title="Details" initialOpen = { false }>
					<TextInput
						label="Name"
						attribute="name"
						attGetSet={getSet}
						placeholder="e.g. Buz Carr"
					/>
					<TextInput
						label="Pronouns"
						attribute="pronouns"
						attGetSet={getSet}
						placeholder="e.g. He/Him"
					/>
					<TextInput
						label="Title"
						attribute="title"
						attGetSet={getSet}
						placeholder="e.g. Marching Band Representative"
					/>
					<TextInput
						label="Email"
						attribute="email"
						attGetSet={getSet}
						placeholder="e.g. President@AtlantaFreedomBands.com"
					/>
				</PanelBody>
			</InspectorControls>
			<div className="afb-profile">
				<img src={attributes.headshotURL} />
				<div className="name">
					{attributes.name}
					<span className="pronouns"> {attributes.pronouns}</span>
				</div>
				<div className="title">{attributes.title}</div>
				{attributes.email && <div>{attributes.email}</div>}
			</div>
		</div>
	);
}

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
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { TextControl } from "@wordpress/components";

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
	const blockProps = useBlockProps();
	return (
		<div {...blockProps}>
			<InspectorControls>
				<div>
					<TextControl
						label="Relative Image URL"
						value={attributes.headshotURL}
						onChange={(val) => setAttributes({ headshotURL: val })}
						placeholder="/wp-content/uploads/2023/05/WhatWeStandFor-768x760_1.png"
					/>
					<TextControl
						label="Name"
						value={attributes.name}
						onChange={(val) => setAttributes({ name: val })}
						placeholder="e.g. Oliver Spirito"
					/>
					<TextControl
						label="Pronouns"
						value={attributes.pronouns}
						onChange={(val) => setAttributes({ pronouns: val })}
						placeholder="e.g. He/Him"
					/>
					<TextControl
						label="Title"
						value={attributes.title}
						onChange={(val) => setAttributes({ title: val })}
						placeholder="e.g. Marching Band Representative"
					/>
					<TextControl
						label="Email"
						value={attributes.email}
						onChange={(val) => setAttributes({ email: val })}
						placeholder="mgrep@AtlantaFreedomBands.com"
					/>
				</div>
			</InspectorControls>
			<div className="afb-profile">
				<img
					src={attributes.headshotURL}
				/>
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

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @param {Object} props            Properties passed to the function.
 * @param {Object} props.attributes Available block attributes.
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {JSX.Element} Element to render.
 */
export default function save( { attributes } ) {
	const blockProps = useBlockProps.save();
	return (
		<div { ...blockProps }>
			<div className="afb-profile">
				<img
					src={ attributes.headshotURL }
					alt={ attributes.name || 'Headshot' }
					className={ 'headshot-img ' + attributes.size }
				/>
				<div className="name">
					{ attributes.name }
					{ attributes.pronouns && (
						<span className="pronouns">
							{ ' ' }
							{ attributes.pronouns }
						</span>
					) }
				</div>
				<div className="title">{ attributes.title }</div>
				{ attributes.email && (
					<a
						className={ 'email ' + attributes.size }
						href={ 'mailto:' + attributes.email }
						target="_blank"
						rel="noopener noreferrer"
					>
						{ attributes.email }
					</a>
				) }
			</div>
		</div>
	);
}

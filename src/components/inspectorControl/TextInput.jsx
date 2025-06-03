import { TextControl } from '@wordpress/components';

const TextInput = ( { label, attribute, attGetSet, placeholder } ) => {
	const [ attGetter, attSetter ] = attGetSet;
	const updateAttributeValue = ( newValue ) => {
		const update = {};
		update[ attribute ] = newValue;
		attSetter( update );
	};
	return (
		<TextControl
			label={ label }
			value={ attGetter[ attribute ] }
			onChange={ updateAttributeValue }
			placeholder={ placeholder }
		/>
	);
};

export default TextInput;

import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { CheckboxControl, PanelBody, SelectControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

registerBlockType( 'akirk/github-contributions', {
	apiVersion: 2,
	edit: function( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		return (
			<>
				<InspectorControls>
					<PanelBody>
						<TextControl
							label={ __( 'Username', 'github-contributions' ) }
							value={ attributes.username }
							onChange={ username => setAttributes( { username } ) }
						/>
						<SelectControl
							label={ __( 'Sort', 'github-contributions' ) }
							onChange={ sort => setAttributes( { sort } ) }
							value={ attributes.sort }
							options={ [
								{
									label: __( 'date', 'github-contributions' ),
									value: 'date',
								},
								{
									label: __( 'count', 'github-contributions' ),
									value: 'count',
								},
							] }
						/>
						<SelectControl
							label={ __( 'Limit', 'github-contributions' ) }
							onChange={ limit => setAttributes( { limit } ) }
							value={ attributes.limit }
							options={ [
								{
									label: 10,
									value: 10,
								},
								{
									label: 20,
									value: 20,
								},
								{
									label: 100,
									value: 100,
								},
							] }
						/>
					</PanelBody>
				</InspectorControls>
				<div {...blockProps}>
					<ServerSideRender
					block="akirk/github-contributions"
						attributes={ attributes }
					/>
				</div>
			</>
		);
	},

    save() {
    	return null;
    },
} );

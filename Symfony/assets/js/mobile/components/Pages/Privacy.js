import React from "react";
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";
import { withStyles } from '@material-ui/core/styles';

import * as Constants from '../../constants';

import htmlprivacy from 'raw-loader!../../raw/privacy.html';

const styles = theme => ({
  terms: {
    backgroundColor:'white',
    padding: '20px 20px 20px 20px',
    marginTop: '-20px',
    fontFamily: Constants.BoringFont,
  },
});

class Tos extends React.Component {
  constructor(props, context) {
    super(props, context);
  }

  render() {

    const { classes } = this.props;

    let html = htmlprivacy.trim().replace(/\s+/g, ' ');

    return (
      <div>
        <Helmet>
          <title>Terms of Service</title>
        </Helmet>
        <div
            className={classes.terms}
            dangerouslySetInnerHTML={{ __html: html}} />
      </div>
    );
  }
}

export default withStyles(styles)(Tos);


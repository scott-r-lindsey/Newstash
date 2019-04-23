import React from "react";
import gql from "graphql-tag";
import { Helmet } from "react-helmet";
import { Link } from "react-router-dom";
import { Query } from "react-apollo";
import { withStyles } from '@material-ui/core/styles';

import * as Constants from '../../constants'
import Loading from "../Trim/Loading";
import workGql from 'raw-loader!../../raw/graphql/work.graphql';
import { fiveStars, generateWorkLink } from "../../util.js";

const styles = theme => ({
});

const workQuery = (id) => {
  return gql(workGql.replace('__WORK_ID__', id));
}

const api = '/api/v1/work';

class Work extends React.Component {
  constructor(props, context) {
    super(props, context);

    this.state = {
      title: 'Books to Love',
    };
  }

  renderWork(work) {
    const { classes } = this.props;
    return (
      <div>
        <Helmet>
          <title>{work.title}</title>
        </Helmet>
        <div>
          <strong>{work.title}</strong>
        </div>
      </div>
    );
  }

  componentDidMount() {
    console.log('did mount');
  }

  render() {

    const id = this.props.match.params.id;
    const {initialProps} = this.props;

    // if initialProps are present and match our needed data
    // we avoid re-requesting that data.  The page was actually
    // rendered on the server side, but React is smart enough to
    // not replace the DOM elements even though we run the
    // render function again.
    let work = false;
    if (  (initialProps.data) &&
          (initialProps.data.work) &&
          (id == initialProps.data.work.id)) {

      work = initialProps.data.work;
    }

    return (
      <div>
        <Helmet>
          <title>{this.state.title}</title>
        </Helmet>

        { ( work ) ?
          <div>
            { this.renderWork(work) }
          </div> :
          <Query query={ workQuery(id) } >

            {({ loading, error, data }) => {
              if (loading) return <Loading />;
              if (error) return <p>Error </p>;

              return this.renderWork(data.work);
            }}
          </Query>
        }
      </div>
    );

  }
}

export default withStyles(styles)(Work);

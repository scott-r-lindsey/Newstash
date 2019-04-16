
import gql from "graphql-tag";
import React from "react";
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";
import { withStyles } from '@material-ui/core/styles';
import { Query } from "react-apollo";

import Loading from "../Trim/Loading";
import * as Constants from '../../constants'
import { fiveStars, generateWorkLink } from "../../util.js";

const workQuery = (id) => {
  return gql`
      {
        work(id: ${id}) {
          id
          title
        }
      }`;
}

const api = '/api/v1/work';

const styles = theme => ({
});

class Work extends React.Component {
  constructor(props, context) {
    super(props, context);

    this.state = {
      title: 'Books to Love',
    };
  }

  componentDidMount() {
    console.log('did mount');
  }

  render() {

    const id = this.props.match.params.id;

    return (
      <div>
        <Helmet>
          <title>{this.state.title}</title>
        </Helmet>

        <Query query={ workQuery(id) } >

          {({ loading, error, data }) => {
            if (loading) return <Loading />;
            if (error) return <p>Error </p>;

            return (
              <div>
                <Helmet>
                  <title>{data.work.title}</title>
                </Helmet>
                <div>
                  <strong>{data.work.title}</strong>
                </div>
              </div>

            );
          }}
        </Query>
      </div>
    );

  }
}

export default withStyles(styles)(Work);
